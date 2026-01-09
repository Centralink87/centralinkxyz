<?php

/**
 * Router pour le serveur PHP built-in
 * Sert les fichiers statiques directement sans passer par Symfony
 */

$requestUri = $_SERVER['REQUEST_URI'] ?? '/';
$requestPath = parse_url($requestUri, PHP_URL_PATH);

// Si c'est un fichier statique (assets, bundles, images, etc.), le servir directement
if (preg_match('#\.(css|js|png|jpg|jpeg|gif|ico|svg|woff|woff2|ttf|eot|map)$#', $requestPath)) {
    $filePath = __DIR__ . $requestPath;
    
    if (file_exists($filePath) && is_file($filePath)) {
        // Déterminer le type MIME
        $mimeTypes = [
            'css' => 'text/css',
            'js' => 'application/javascript',
            'png' => 'image/png',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'gif' => 'image/gif',
            'ico' => 'image/x-icon',
            'svg' => 'image/svg+xml',
            'woff' => 'font/woff',
            'woff2' => 'font/woff2',
            'ttf' => 'font/ttf',
            'eot' => 'application/vnd.ms-fontobject',
            'map' => 'application/json',
        ];
        
        $extension = pathinfo($filePath, PATHINFO_EXTENSION);
        $mimeType = $mimeTypes[$extension] ?? 'application/octet-stream';
        
        header('Content-Type: ' . $mimeType);
        header('Content-Length: ' . filesize($filePath));
        readfile($filePath);
        exit;
    }
}

// Sinon, passer à Symfony (via index.php)
// Le serveur PHP built-in avec un router appelle le router pour chaque requête
// Si le router retourne false, le serveur essaie de servir le fichier directement
// Pour que Symfony gère les routes, on doit inclure index.php
// Mais index.php retourne une fonction, donc on doit utiliser le runtime Symfony
require_once dirname(__DIR__).'/vendor/autoload_runtime.php';

$kernelLoader = require __DIR__ . '/index.php';
if (is_callable($kernelLoader)) {
    $kernel = $kernelLoader($_SERVER);
    $request = \Symfony\Component\HttpFoundation\Request::createFromGlobals();
    $response = $kernel->handle($request);
    $response->send();
    $kernel->terminate($request, $response);
    exit;
}

// Fallback
return false;

