<?php

// Vérification de l'enregistrement de l'utilisateur
require_once 'config/auth.php';
require_once 'config/session.php';

// Initialisation de la variable message
$message = "";

// Récupération de la BDD
$pr = new ProductRepository($pdo);
$cr = new CategoryRepository($pdo);
$ur = new UserRepository($pdo);

$categories = $cr->findAll()->fetchAll();

// Identification Modification/Nouvelle catégorie
if (isset($_GET["id"])) {
    if (empty($_GET["id"])) {
        header("location:?page=myProduct");
    } else {
        $productId = $_GET["id"];
        $productData = $pr->findBy($productId, "(0, 1)")->fetch();
        if ($productData->seller !== $_SESSION['users']->id) {
            header("location:?page=myProducts");
        } else {
            $product = new Product(
                $productData->id,
                $productData->categoryId,
                $productData->name,
                $productData->slug,
                $productData->description,
                $productData->seller,
                $productData->price / 100,
                $productData->trash
            );
        }
    }
}

// Traitement de la requête
if (isset($_POST["request"])) {

    // Vérification de la saisie des informations
    $msgDetails = "";
    $isValid = true;
    if (!(isset($_POST["productName"]) && !empty($_POST["productName"]))) {
        $isValid = false;
        $msgDetails = "nom manquant";
    }
    if (!(isset($_POST["productCategoryId"]) && !empty($_POST["productCategoryId"]))) {
        $isValid = false;
        $msgDetails = "catégorie manquante";
    }
    if (!(isset($_POST["productSlug"]) && !empty($_POST["productSlug"]))) {
        $isValid = false;
        $alt = $msgDetails === "" ? "" : ", ";
        $msgDetails .= $alt . "slug manquant";
    }
    if (!(isset($_POST["productDescription"]) && !empty($_POST["productDescription"]))) {
        $isValid = false;
        $alt = $msgDetails === "" ? "" : ", ";
        $msgDetails .= $alt . "description manquante";
    }
    if (!(isset($_POST["productPrice"]) && !empty($_POST["productPrice"]))) {
        $isValid = false;
        $alt = $msgDetails === "" ? "" : ", ";
        $msgDetails .= $alt . "prix manquant";
    }
    
    // Envoi de la requête
    if ($isValid) {
        $product = new Product(
            isset($_GET["id"]) ? $_GET["id"] : null,
            $_POST["productCategoryId"],
            $_POST["productName"],
            $_POST["productSlug"],
            $_POST["productDescription"],
            $_SESSION['users']->id,
            str_replace(",", ".", $_POST["productPrice"]) * 100,
            isset($_POST["productTrash"]) ? 1 : 0
        );

        // Modification catégorie
        if (isset($_GET["id"])) {
            $pr->update($product);

        // Ajout catégorie
        } else {
            $pr->insert($product);
            $productId = $pr->lastInsert();
        }

        // Redirection vers page catégories
        header("location:?page=myProducts");

    } else {
        $message = "Informations incorrectes : " . $msgDetails;
    }
}