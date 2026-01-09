<?php

namespace App\Controller\Admin;

use App\Entity\Transaction;
use App\Enum\CryptoType;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\BooleanFilter;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\Response;

class TransactionCrudController extends AbstractCrudController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private AdminUrlGenerator $adminUrlGenerator
    ) {}

    public static function getEntityFqcn(): string
    {
        return Transaction::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Transaction')
            ->setEntityLabelInPlural('Transactions')
            ->setPageTitle('index', 'Gestion des Transactions')
            ->setPageTitle('detail', fn (Transaction $t) => sprintf('%s - %s %s', $t->getCryptoType()?->symbol() ?? 'N/A', $t->getAmount(), $t->getUser()?->getFullName()))
            ->setDefaultSort(['createdAt' => 'DESC'])
            ->showEntityActionsInlined();
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->hideOnForm();
        
        yield AssociationField::new('user', 'Utilisateur')
            ->setFormTypeOption('choice_label', 'fullName');
        
        yield ChoiceField::new('cryptoType', 'Crypto')
            ->setChoices([
                'Bitcoin' => CryptoType::BTC,
                'Ethereum' => CryptoType::ETH,
                'USD Coin' => CryptoType::USDC,
                'Tether' => CryptoType::USDT
            ]);
        
        yield NumberField::new('amount', 'Montant')
            ->setNumDecimals(8);
        
        yield NumberField::new('entryPrice', 'Prix entrée ($)')
            ->setNumDecimals(2);
        
        yield NumberField::new('exitPrice', 'Prix sortie ($)')
            ->setNumDecimals(2)
            ->hideOnIndex();
        
        yield DateTimeField::new('transactionDate', 'Date transaction')
            ->setFormat('dd/MM/yyyy HH:mm');
        
        yield BooleanField::new('isValidated', 'Validée')
            ->renderAsSwitch(true);
        
        yield DateTimeField::new('validatedAt', 'Validée le')
            ->setFormat('dd/MM/yyyy HH:mm')
            ->hideOnForm()
            ->hideOnIndex();
        
        yield DateTimeField::new('createdAt', 'Créée le')
            ->setFormat('dd/MM/yyyy HH:mm')
            ->hideOnForm();
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(BooleanFilter::new('isValidated', 'Validée'))
            ->add('user');
    }

    public function configureActions(Actions $actions): Actions
    {
        $validateAction = Action::new('validate', 'Valider', 'fa fa-check')
            ->linkToCrudAction('validateTransaction')
            ->setCssClass('btn btn-success')
            ->displayIf(fn (Transaction $t) => !$t->isValidated());
        
        $rejectAction = Action::new('reject', 'Rejeter', 'fa fa-times')
            ->linkToCrudAction('rejectTransaction')
            ->setCssClass('btn btn-danger')
            ->displayIf(fn (Transaction $t) => !$t->isValidated());

        return $actions
            ->add(Crud::PAGE_INDEX, $validateAction)
            ->add(Crud::PAGE_INDEX, $rejectAction)
            ->add(Crud::PAGE_DETAIL, $validateAction)
            ->add(Crud::PAGE_DETAIL, $rejectAction)
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->reorder(Crud::PAGE_INDEX, [Action::DETAIL, 'validate', 'reject', Action::EDIT, Action::DELETE]);
    }

    public function validateTransaction(AdminContext $context): Response
    {
        /** @var Transaction $transaction */
        $transaction = $context->getEntity()->getInstance();
        
        $transaction->setIsValidated(true);
        $this->entityManager->flush();
        
        $this->addFlash('success', sprintf(
            'Transaction #%d validée avec succès !',
            $transaction->getId()
        ));

        return $this->redirect(
            $this->adminUrlGenerator
                ->setController(self::class)
                ->setAction(Action::INDEX)
                ->generateUrl()
        );
    }

    public function rejectTransaction(AdminContext $context): Response
    {
        /** @var Transaction $transaction */
        $transaction = $context->getEntity()->getInstance();
        
        $this->entityManager->remove($transaction);
        $this->entityManager->flush();
        
        $this->addFlash('warning', sprintf(
            'Transaction #%d rejetée et supprimée.',
            $transaction->getId()
        ));

        return $this->redirect(
            $this->adminUrlGenerator
                ->setController(self::class)
                ->setAction(Action::INDEX)
                ->generateUrl()
        );
    }
}
