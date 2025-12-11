<?php

require("config.php");
session_start();
$action = isset($_GET['action']) ? $_GET['action'] : "";
$username = isset($_SESSION['username']) ? $_SESSION['username'] : "";

if ($action != "login" && $action != "logout" && !$username) {
    login();
    exit;
}

switch ($action) {
    case 'login':
        login();
        break;
    case 'logout':
        logout();
        break;
    case 'newArticle':
        newArticle();
        break;
    case 'editArticle':
        editArticle();
        break;
    case 'deleteArticle':
        deleteArticle();
        break;
    case 'listCategories':
        listCategories();
        break;
    case 'newCategory':
        newCategory();
        break;
    case 'editCategory':
        editCategory();
        break;
    case 'deleteCategory':
        deleteCategory();
        break;
    case 'listUsers':
        listUsers();
        break;
    case 'newUser':
        newUser();
        break;
    case 'editUser':
        editUser();
        break;
    case 'deleteUser':
        deleteUser();
        break;
    default:
        listArticles();
}

/**
 * Авторизация пользователя (админа) -- установка значения в сессию
 */
function login() {

    $results = array();
    $results['pageTitle'] = "Admin Login | Widget News";

    if (isset($_POST['login'])) {

        // Пользователь получает форму входа: попытка авторизировать пользователя
    
        if ($_POST['username'] == ADMIN_USERNAME
                && $_POST['password'] == ADMIN_PASSWORD) {
    
          // Вход прошел успешно: создаем сессию и перенаправляем на страницу администратора
          $_SESSION['username'] = ADMIN_USERNAME;
          header( "Location: admin.php");
    
        } else {
            // Проверяем, является ли логин не "admin"
            if ($_POST['username'] != ADMIN_USERNAME) {
                // Используем для проверки логина/пароля созданную сущность User
                $user = User::authenticate($_POST['username'], $_POST['password']);
                
                if ($user) {
                    // Пользователь аутентифицирован и активен
                    $_SESSION['username'] = $user->login;
                    header("Location: admin.php");
                } else {
                    // Пользователь не найден, пароль неверный или пользователь не активен
                    $results['errorMessage'] = "Неправильный логин или пароль, попробуйте ещё раз.";
                    require(TEMPLATE_PATH . "/admin/loginForm.php");
                }
            } else {
                // Ошибка входа: выводим сообщение об ошибке для пользователя
                $results['errorMessage'] = "Неправильный пароль, попробуйте ещё раз.";
                require(TEMPLATE_PATH . "/admin/loginForm.php");
            }
        }

    } else {

      // Пользователь еще не получил форму: выводим форму
      require(TEMPLATE_PATH . "/admin/loginForm.php");
    }

}


function logout() {
    unset( $_SESSION['username'] );
    header( "Location: admin.php" );
}


function newArticle() {
	  
    $results = array();
    $results['pageTitle'] = "New Article";
    $results['formAction'] = "newArticle";

    if ( isset( $_POST['saveChanges'] ) ) {
//            echo "<pre>";
//            print_r($results);
//            print_r($_POST);
//            echo "<pre>";
//            В $_POST данные о статье сохраняются корректно
        // Пользователь получает форму редактирования статьи: сохраняем новую статью
        $article = new Article();
        $article->storeFormValues( $_POST );
//            echo "<pre>";
//            print_r($article);
//            echo "<pre>";
//            А здесь данные массива $article уже неполные(есть только Число от даты, категория и полный текст статьи)          
        $article->insert();
        header( "Location: admin.php?status=changesSaved" );

    } elseif ( isset( $_POST['cancel'] ) ) {

        // Пользователь сбросил результаты редактирования: возвращаемся к списку статей
        header( "Location: admin.php" );
    } else {

        // Пользователь еще не получил форму редактирования: выводим форму
        $results['article'] = new Article;
        $data = Category::getList();
        $results['categories'] = $data['results'];
        require( TEMPLATE_PATH . "/admin/editArticle.php" );
    }
}


/**
 * Редактирование статьи
 * 
 * @return null
 */
function editArticle() {
	  
    $results = array();
    $results['pageTitle'] = "Edit Article";
    $results['formAction'] = "editArticle";

    if (isset($_POST['saveChanges'])) {

        // Пользователь получил форму редактирования статьи: сохраняем изменения
        if ( !$article = Article::getById( (int)$_POST['articleId'] ) ) {
            header( "Location: admin.php?error=articleNotFound" );
            return;
        }

        $article->storeFormValues( $_POST );
        $article->update();
        header( "Location: admin.php?status=changesSaved" );

    } elseif ( isset( $_POST['cancel'] ) ) {

        // Пользователь отказался от результатов редактирования: возвращаемся к списку статей
        header( "Location: admin.php" );
    } else {

        // Пользвоатель еще не получил форму редактирования: выводим форму
        $results['article'] = Article::getById((int)$_GET['articleId']);
        $data = Category::getList();
        $results['categories'] = $data['results'];
        require(TEMPLATE_PATH . "/admin/editArticle.php");
    }

}


function deleteArticle() {

    if ( !$article = Article::getById( (int)$_GET['articleId'] ) ) {
        header( "Location: admin.php?error=articleNotFound" );
        return;
    }

    $article->delete();
    header( "Location: admin.php?status=articleDeleted" );
}


function listArticles() {
    $results = array();
    
    $data = Article::getList();
    $results['articles'] = $data['results'];
    $results['totalRows'] = $data['totalRows'];
    
    $data = Category::getList();
    $results['categories'] = array();
    foreach ($data['results'] as $category) { 
        $results['categories'][$category->id] = $category;
    }
    
    $results['pageTitle'] = "Все статьи";

    if (isset($_GET['error'])) { // вывод сообщения об ошибке (если есть)
        if ($_GET['error'] == "articleNotFound") 
            $results['errorMessage'] = "Error: Article not found.";
    }

    if (isset($_GET['status'])) { // вывод сообщения (если есть)
        if ($_GET['status'] == "changesSaved") {
            $results['statusMessage'] = "Your changes have been saved.";
        }
        if ($_GET['status'] == "articleDeleted")  {
            $results['statusMessage'] = "Article deleted.";
        }
    }

    require(TEMPLATE_PATH . "/admin/listArticles.php" );
}

function listCategories() {
    $results = array();
    $data = Category::getList();
    $results['categories'] = $data['results'];
    $results['totalRows'] = $data['totalRows'];
    $results['pageTitle'] = "Article Categories";

    if ( isset( $_GET['error'] ) ) {
        if ( $_GET['error'] == "categoryNotFound" ) $results['errorMessage'] = "Error: Category not found.";
        if ( $_GET['error'] == "categoryContainsArticles" ) $results['errorMessage'] = "Error: Category contains articles. Delete the articles, or assign them to another category, before deleting this category.";
    }

    if ( isset( $_GET['status'] ) ) {
        if ( $_GET['status'] == "changesSaved" ) $results['statusMessage'] = "Your changes have been saved.";
        if ( $_GET['status'] == "categoryDeleted" ) $results['statusMessage'] = "Category deleted.";
    }

    require( TEMPLATE_PATH . "/admin/listCategories.php" );
}
	  
	  
function newCategory() {

    $results = array();
    $results['pageTitle'] = "New Article Category";
    $results['formAction'] = "newCategory";

    if ( isset( $_POST['saveChanges'] ) ) {

        // User has posted the category edit form: save the new category
        $category = new Category;
        $category->storeFormValues( $_POST );
        $category->insert();
        header( "Location: admin.php?action=listCategories&status=changesSaved" );

    } elseif ( isset( $_POST['cancel'] ) ) {

        // User has cancelled their edits: return to the category list
        header( "Location: admin.php?action=listCategories" );
    } else {

        // User has not posted the category edit form yet: display the form
        $results['category'] = new Category;
        require( TEMPLATE_PATH . "/admin/editCategory.php" );
    }

}


function editCategory() {

    $results = array();
    $results['pageTitle'] = "Edit Article Category";
    $results['formAction'] = "editCategory";

    if ( isset( $_POST['saveChanges'] ) ) {

        // User has posted the category edit form: save the category changes

        if ( !$category = Category::getById( (int)$_POST['categoryId'] ) ) {
          header( "Location: admin.php?action=listCategories&error=categoryNotFound" );
          return;
        }

        $category->storeFormValues( $_POST );
        $category->update();
        header( "Location: admin.php?action=listCategories&status=changesSaved" );

    } elseif ( isset( $_POST['cancel'] ) ) {

        // User has cancelled their edits: return to the category list
        header( "Location: admin.php?action=listCategories" );
    } else {

        // User has not posted the category edit form yet: display the form
        $results['category'] = Category::getById( (int)$_GET['categoryId'] );
        require( TEMPLATE_PATH . "/admin/editCategory.php" );
    }

}


function deleteCategory() {

    if ( !$category = Category::getById( (int)$_GET['categoryId'] ) ) {
        header( "Location: admin.php?action=listCategories&error=categoryNotFound" );
        return;
    }

    $articles = Article::getList( 1000000, $category->id );

    if ( $articles['totalRows'] > 0 ) {
        header( "Location: admin.php?action=listCategories&error=categoryContainsArticles" );
        return;
    }
$category->delete();
    header( "Location: admin.php?action=listCategories&status=categoryDeleted" );
}

/**
 * Вывод списка пользователей
 */
function listUsers() {
    $results = array();
    $data = User::getList();
    $results['users'] = $data['results'];
    $results['totalRows'] = $data['totalRows'];
    $results['pageTitle'] = "Управление пользователями";

    if (isset($_GET['status'])) { // вывод сообщения (если есть)
        if ($_GET['status'] == "changesSaved") {
            $results['statusMessage'] = "Изменения сохранены.";
        }
        if ($_GET['status'] == "userDeleted")  {
            $results['statusMessage'] = "Пользователь удален.";
        }
    }

    require(TEMPLATE_PATH . "/admin/listUsers.php");
}

/**
 * Создание нового пользователя
 */
function newUser() {
    $results = array();
    $results['pageTitle'] = "Новый пользователь";
    $results['formAction'] = "newUser";

    if (isset($_POST['saveChanges'])) {
        // Пользователь отправил форму редактирования: сохраняем нового пользователя
        $user = new User();
        $user->storeFormValues($_POST);
        
        // Проверяем, существует ли уже пользователь с таким логином
        if (User::loginExists($_POST['login'])) {
            $results['errorMessage'] = "Пользователь с таким логином уже существует.";
            require(TEMPLATE_PATH . "/admin/editUser.php");
            return;
        }
        
        $user->insert();
        header("Location: admin.php?action=listUsers&status=changesSaved");
    } elseif (isset($_POST['cancel'])) {
        // Пользователь отменил редактирование: возвращаемся к списку пользователей
        header("Location: admin.php?action=listUsers");
    } else {
        // Пользователь еще не отправлял форму: выводим форму
        $results['user'] = new User;
        require(TEMPLATE_PATH . "/admin/editUser.php");
    }
}

/**
 * Редактирование пользователя
 */
function editUser() {
    $results = array();
    $results['pageTitle'] = "Редактировать пользователя";
    $results['formAction'] = "editUser";

    if (isset($_POST['saveChanges'])) {
        // Пользователь отправил форму редактирования: сохраняем изменения
        if (!$user = User::getById((int)$_POST['userId'])) {
            header("Location: admin.php?action=listUsers&error=userNotFound");
            return;
        }

        $user->storeFormValues($_POST);
        
        // Проверяем, существует ли уже пользователь с таким логином (исключая текущего пользователя)
        if (User::loginExists($_POST['login'], (int)$_POST['userId'])) {
            $results['errorMessage'] = "Пользователь с таким логином уже существует.";
            require(TEMPLATE_PATH . "/admin/editUser.php");
            return;
        }
        
        $user->update();
        header("Location: admin.php?action=listUsers&status=changesSaved");
    } elseif (isset($_POST['cancel'])) {
        // Пользователь отменил редактирование: возвращаемся к списку пользователей
        header("Location: admin.php?action=listUsers");
    } else {
        // Пользователь еще не отправлял форму: выводим форму
        $results['user'] = User::getById((int)$_GET['userId']);
        require(TEMPLATE_PATH . "/admin/editUser.php");
    }
}

/**
 * Удаление пользователя
 */
function deleteUser() {
    if (!$user = User::getById((int)$_GET['userId'])) {
        header("Location: admin.php?action=listUsers&error=userNotFound");
        return;
    }

    $user->delete();
    header("Location: admin.php?action=listUsers&status=userDeleted");
}

        