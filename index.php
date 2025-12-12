<?php

//phpinfo(); die();

require("config.php");
$action = isset($_GET['action']) ? $_GET['action'] : "";

switch ($action) {
  case 'archive':
    archive();
    break;
  case 'viewArticle':
    viewArticle();
    break;
 case 'viewArticlesBySubcategory':
    viewArticlesBySubcategory();
    break;
  default:
    homepage();
}

function archive()
{
    $results = [];
    
    $categoryId = ( isset( $_GET['categoryId'] ) && $_GET['categoryId'] ) ? (int)$_GET['categoryId'] : null;
    $subcategoryId = ( isset( $_GET['subcategoryId'] ) && $_GET['subcategoryId'] ) ? (int)$_GET['subcategoryId'] : null;
    
    $results['category'] = Category::getById( $categoryId );
    $results['subcategory'] = Subcategory::getById( $subcategoryId );
    
    // Получаем статьи, фильтруя по категории и/или подкатегории
    $data = Article::getList(100); // Получаем все статьи
    $filteredArticles = array_filter($data['results'], function($article) use ($categoryId, $subcategoryId) {
        $categoryMatch = !$categoryId || $article->categoryId == $categoryId;
        $subcategoryMatch = !$subcategoryId || $article->subcategoryId == $subcategoryId;
        return $categoryMatch && $subcategoryMatch;
    });
    
    $results['articles'] = $filteredArticles;
    $results['totalRows'] = count($filteredArticles);
    
    $data = Category::getList();
    $results['categories'] = array();
    
    foreach ( $data['results'] as $category ) {
        $results['categories'][$category->id] = $category;
    }
    
    // Получаем подкатегории для отображения
    $subcategoryData = Subcategory::getList();
    $results['subcategories'] = array();
    foreach ($subcategoryData['results'] as $subcategory) {
        $results['subcategories'][$subcategory->id] = $subcategory;
    }
    
    if ($results['subcategory']) {
        $results['pageHeading'] = $results['subcategory']->name;
    } else if ($results['category']) {
        $results['pageHeading'] = $results['category']->name;
    } else {
        $results['pageHeading'] = "Article Archive";
    }
    
    $results['pageTitle'] = $results['pageHeading'] . " | Widget News";
    
    require( TEMPLATE_PATH . "/archive.php" );
}

/**
 * Загрузка страницы с конкретной статьёй
 *
 * @return null
 */
function viewArticle()
{
    if ( !isset($_GET["articleId"]) || !$_GET["articleId"] ) {
      homepage();
      return;
    }

    $results = array();
    $results['article'] = Article::getById((int)$_GET["articleId"]);
    $results['category'] = Category::getById($results['article']->categoryId);
    
    // Получаем подкатегорию, если она есть
    if ($results['article']->subcategoryId) {
        $results['subcategory'] = Subcategory::getById($results['article']->subcategoryId);
    } else {
        $results['subcategory'] = null;
    }
    
    $results['pageTitle'] = $results['article']->title . " | Простая CMS";
    
    require(TEMPLATE_PATH . "/viewArticle.php");
}

/**
 * Вывод домашней ("главной") страницы сайта
 */
function homepage()
{
    $results = array();
    $data = Article::getList(HOMEPAGE_NUM_ARTICLES, null, 1);
    $results['articles'] = $data['results'];
    $results['totalRows'] = $data['totalRows'];
    
    $data = Category::getList();
    $results['categories'] = array();
    foreach ( $data['results'] as $category ) {
        $results['categories'][$category->id] = $category;
    }
    
    // Получаем подкатегории для отображения в шаблонах
    $subcategoryData = Subcategory::getList();
    $results['subcategories'] = array();
    foreach ($subcategoryData['results'] as $subcategory) {
        $results['subcategories'][$subcategory->id] = $subcategory;
    }
    
    $results['pageTitle'] = "Простая CMS на PHP";
    
//    echo "<pre>";
//    print_r($data);
//    echo "</pre>";
//    die();
    
    require(TEMPLATE_PATH . "/homepage.php");
    
}

/**
 * Вывод статей по выбранной подкатегории
 */
function viewArticlesBySubcategory()
{
    $results = [];
    
    $subcategoryId = (isset($_GET['subcategoryId']) && $_GET['subcategoryId']) ? (int)$_GET['subcategoryId'] : null;
    
    $results['subcategory'] = Subcategory::getById($subcategoryId);
    
    if (!$results['subcategory']) {
        homepage();
        return;
    }
    
    // Получаем все статьи и фильтруем по подкатегории
    $allArticles = Article::getList(10000); // Получаем все статьи
    $results['articles'] = array_filter($allArticles['results'], function($article) use ($subcategoryId) {
        return $article->subcategoryId == $subcategoryId;
    });
    
    // Получаем все категории и подкатегории для отображения в шаблоне
    $categoryData = Category::getList();
    $results['categories'] = array();
    foreach ($categoryData['results'] as $category) {
        $results['categories'][$category->id] = $category;
    }
    
    $subcategoryData = Subcategory::getList();
    $results['subcategories'] = array();
    foreach ($subcategoryData['results'] as $subcategory) {
        $results['subcategories'][$subcategory->id] = $subcategory;
    }
    
    $results['pageHeading'] = "Articles in Subcategory: " . $results['subcategory']->name;
    $results['pageTitle'] = $results['pageHeading'] . " | Widget News";
    
    require(TEMPLATE_PATH . "/listArticlesBySubcategory.php");
}
