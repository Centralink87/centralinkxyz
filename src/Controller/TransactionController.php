<?php

namespace App\Controller;

use App\Entity\Transaction;
use App\Entity\User;
use App\Form\TransactionExitType;
use App\Form\TransactionType;
use App\Repository\TransactionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/transactions')]
#[IsGranted('ROLE_ADMIN')]
final class TransactionController extends AbstractController
{
    #[Route('', name: 'app_transaction_index', methods: ['GET'])]
    public function index(TransactionRepository $transactionRepository): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        
        // Transactions validées
        $validatedTransactions = $transactionRepository->findValidatedByUser($user);
        
        // Transactions en attente de validation
        $pendingTransactions = $transactionRepository->findPendingByUser($user);

        // Calcul des stats (seulement sur les transactions validées)
        $totalInvested = 0;
        $totalProfitLoss = 0;
        $closedTransactions = 0;
        
        // Préparation des données pour le graphique P&L
        $pnlData = [];
        $cumulativePnl = 0;
        
        // Récupérer les transactions clôturées triées par date
        $closedTransactionsList = array_filter($validatedTransactions, fn($t) => $t->getExitPrice() !== null);
        usort($closedTransactionsList, fn($a, $b) => $a->getTransactionDate() <=> $b->getTransactionDate());

        foreach ($validatedTransactions as $transaction) {
            $entryValue = (float) $transaction->getEntryPrice() * (float) $transaction->getAmount();
            $totalInvested += $entryValue;

            if ($transaction->getExitPrice() !== null) {
                $closedTransactions++;
                $profitLoss = (float) $transaction->getProfitLoss();
                $totalProfitLoss += $profitLoss;
            }
        }
        
        // Construire les données pour le graphique (P&L cumulatif)
        foreach ($closedTransactionsList as $transaction) {
            $profitLoss = (float) $transaction->getProfitLoss();
            $cumulativePnl += $profitLoss;
            
            $pnlData[] = [
                'date' => $transaction->getTransactionDate()->format('Y-m-d'),
                'dateFormatted' => $transaction->getTransactionDate()->format('d/m/Y'),
                'pnl' => $profitLoss,
                'cumulativePnl' => $cumulativePnl,
            ];
        }

        return $this->render('transaction/index.html.twig', [
            'transactions' => $validatedTransactions,
            'pendingTransactions' => $pendingTransactions,
            'totalInvested' => $totalInvested,
            'totalProfitLoss' => $totalProfitLoss,
            'closedTransactions' => $closedTransactions,
            'pnlChartData' => $pnlData,
        ]);
    }

    #[Route('/new', name: 'app_transaction_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $transaction = new Transaction();
        $transaction->setTransactionDate(new \DateTimeImmutable());

        $form = $this->createForm(TransactionType::class, $transaction);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var User $user */
            $user = $this->getUser();
            $transaction->setUser($user);
            // La transaction n'est PAS validée par défaut
            $transaction->setIsValidated(false);

            $entityManager->persist($transaction);
            $entityManager->flush();

            $this->addFlash('success', 'Transaction créée ! Elle sera visible après validation par un administrateur.');

            return $this->redirectToRoute('app_transaction_index');
        }

        return $this->render('transaction/new.html.twig', [
            'transaction' => $transaction,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_transaction_show', methods: ['GET'])]
    public function show(Transaction $transaction): Response
    {
        // Vérifier que la transaction appartient à l'utilisateur connecté
        $this->denyAccessUnlessGranted('view', $transaction);

        return $this->render('transaction/show.html.twig', [
            'transaction' => $transaction,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_transaction_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Transaction $transaction, EntityManagerInterface $entityManager): Response
    {
        // Vérifier que la transaction appartient à l'utilisateur connecté
        $this->denyAccessUnlessGranted('edit', $transaction);

        $form = $this->createForm(TransactionType::class, $transaction);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Transaction modifiée avec succès !');

            return $this->redirectToRoute('app_transaction_index');
        }

        return $this->render('transaction/edit.html.twig', [
            'transaction' => $transaction,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/close', name: 'app_transaction_close', methods: ['GET', 'POST'])]
    public function close(Request $request, Transaction $transaction, EntityManagerInterface $entityManager): Response
    {
        // Vérifier que la transaction appartient à l'utilisateur connecté
        $this->denyAccessUnlessGranted('edit', $transaction);

        // Ne peut clôturer que si validée
        if (!$transaction->isValidated()) {
            $this->addFlash('warning', 'Cette transaction doit d\'abord être validée par un administrateur.');
            return $this->redirectToRoute('app_transaction_index');
        }

        $form = $this->createForm(TransactionExitType::class, $transaction);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $profitLoss = $transaction->getProfitLoss();
            $isProfit = (float) $profitLoss >= 0;

            $this->addFlash(
                $isProfit ? 'success' : 'warning',
                sprintf(
                    'Transaction clôturée ! %s : $%s',
                    $isProfit ? 'Bénéfice' : 'Perte',
                    number_format(abs((float) $profitLoss), 2)
                )
            );

            return $this->redirectToRoute('app_transaction_show', ['id' => $transaction->getId()]);
        }

        return $this->render('transaction/close.html.twig', [
            'transaction' => $transaction,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/delete', name: 'app_transaction_delete', methods: ['POST'])]
    public function delete(Request $request, Transaction $transaction, EntityManagerInterface $entityManager): Response
    {
        // Vérifier que la transaction appartient à l'utilisateur connecté
        $this->denyAccessUnlessGranted('delete', $transaction);

        if ($this->isCsrfTokenValid('delete' . $transaction->getId(), $request->request->get('_token'))) {
            $entityManager->remove($transaction);
            $entityManager->flush();

            $this->addFlash('success', 'Transaction supprimée.');
        }

        return $this->redirectToRoute('app_transaction_index');
    }
}
