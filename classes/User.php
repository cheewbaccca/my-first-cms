<?php

/**
 * Класс для обработки пользователей
 */
class User
{
    // Свойства
    /**
    * @var int ID пользователя из базы данных
    */
    public $id = null;

    /**
    * @var string Логин пользователя
    */
    public $login = null;

    /**
    * @var string Пароль пользователя (зашифрованный)
    */
    public $password = null;

    /**
    * @var int Флаг активности пользователя (1 - активен, 0 - не активен)
    */
    public $isActive = null;

    /**
     * Создаст объект пользователя
     * 
     * @param array $data массив значений (столбцов) строки таблицы пользователей
     */
    public function __construct($data=array())
    {
        if (isset($data['id'])) {
            $this->id = (int) $data['id'];
        }
        
        if (isset($data['login'])) {
            $this->login = $data['login'];        
        }
        
        if (isset($data['password'])) {
            $this->password = $data['password'];      
        }
        
        if (isset($data['isActive'])) {
            $this->isActive = (int) $data['isActive'];         
        } else {
            $this->isActive = 0;
        }
    }

    /**
    * Устанавливаем свойства с помощью значений формы редактирования записи в заданном массиве
    *
    * @param assoc Значения записи формы
    */
    public function storeFormValues ($params) {

        // Сохраняем все параметры
        $this->__construct($params);

        // Шифруем пароль, если он был передан
        if (isset($params['password']) && !empty($params['password'])) {
            $this->password = password_hash($params['password'], PASSWORD_DEFAULT);
        } else {
            // Если пароль не передан или пустой, не изменяем его
            if (isset($params['id'])) {
                // При редактировании получаем текущий пароль из БД
                $current_user = self::getById($params['id']);
                if ($current_user) {
                    $this->password = $current_user->password;
                }
            }
        }
    }


    /**
    * Возвращаем объект пользователя соответствующий заданному ID
    *
    * @param int ID пользователя
    * @return User|false Объект пользователя или false, если запись не найдена или возникли проблемы
    */
    public static function getById($id) {
        $conn = new PDO(DB_DSN, DB_USERNAME, DB_PASSWORD);
        $sql = "SELECT *, is_active as isActive FROM users WHERE id = :id";
        $st = $conn->prepare($sql);
        $st->bindValue(":id", $id, PDO::PARAM_INT);
        $st->execute();

        $row = $st->fetch();
        $conn = null;
        
        if ($row) {
            return new User($row);
        }
        return false;
    }

    /**
    * Возвращает все (или диапазон) объекты User из базы данных
    *
    * @param int $numRows Количество возвращаемых строк (по умолчанию = 1000000)
    * @param string $order Столбец, по которому выполняется сортировка пользователей (по умолчанию = "login ASC")
    * @return Array|false Двух элементный массив: results => массив объектов User; totalRows => общее количество строк
    */
    public static function getList($numRows=10000, $order="login ASC") 
    {
        $conn = new PDO(DB_DSN, DB_USERNAME, DB_PASSWORD);
        
        $sql = "SELECT SQL_CALC_FOUND_ROWS *, is_active as isActive
                FROM users
                ORDER BY $order LIMIT :numRows";
        
        $st = $conn->prepare($sql);
        $st->bindValue(":numRows", $numRows, PDO::PARAM_INT);
        $st->execute();
        $list = array();

        while ($row = $st->fetch()) {
            $user = new User($row);
            $list[] = $user;
        }

        // Получаем общее количество пользователей, которые соответствуют критерию
        $sql = "SELECT FOUND_ROWS() AS totalRows";
        $totalRows = $conn->query($sql)->fetch();
        $conn = null;
        
        return (array(
            "results" => $list, 
            "totalRows" => $totalRows[0]
            ) 
        );
    }

    /**
    * Вставляем текущий объект User в базу данных, устанавливаем его ID.
    */
    public function insert() {

        // Есть уже у объекта User ID?
        if (!is_null($this->id)) trigger_error("User::insert(): Attempt to insert an User object that already has its ID property set (to $this->id).", E_USER_ERROR);

        // Вставляем пользователя
        $conn = new PDO(DB_DSN, DB_USERNAME, DB_PASSWORD);
        $sql = "INSERT INTO users (login, password, is_active) VALUES (:login, :password, :isActive)";
        $st = $conn->prepare($sql);
        $st->bindValue(":login", $this->login, PDO::PARAM_STR);
        $st->bindValue(":password", $this->password, PDO::PARAM_STR);
        $st->bindValue(":isActive", $this->isActive, PDO::PARAM_INT);
        $st->execute();
        $this->id = $conn->lastInsertId();
        $conn = null;
    }

    /**
    * Обновляем текущий объект пользователя в базе данных
    */
    public function update() {

      // Есть ли у объекта пользователя ID?
      if (is_null($this->id)) trigger_error("User::update(): Attempt to update an User object that does not have its ID property set.", E_USER_ERROR);

      // Обновляем пользователя
      $conn = new PDO(DB_DSN, DB_USERNAME, DB_PASSWORD);
      $sql = "UPDATE users SET login=:login, password=:password, is_active=:isActive WHERE id = :id";
      $st = $conn->prepare($sql);
      $st->bindValue(":login", $this->login, PDO::PARAM_STR);
      $st->bindValue(":password", $this->password, PDO::PARAM_STR);
      $st->bindValue(":isActive", $this->isActive, PDO::PARAM_INT);
      $st->bindValue(":id", $this->id, PDO::PARAM_INT);
      $st->execute();
      $conn = null;
  }


    /**
    * Удаляем текущий объект пользователя из базы данных
    */
    public function delete() {

      // Есть ли у объекта пользователя ID?
      if (is_null($this->id)) trigger_error("User::delete(): Attempt to delete an User object that does not have its ID property set.", E_USER_ERROR);

      // Удаляем пользователя
      $conn = new PDO(DB_DSN, DB_USERNAME, DB_PASSWORD);
      $st = $conn->prepare("DELETE FROM users WHERE id = :id LIMIT 1");
      $st->bindValue(":id", $this->id, PDO::PARAM_INT);
      $st->execute();
      $conn = null;
    }
    
    /**
     * Проверяет, существует ли пользователь с указанным логином
     * 
     * @param string $login Логин для проверки
     * @param int $excludeUserId ID пользователя для исключения из проверки (при редактировании)
     * @return bool true если логин уже существует, иначе false
     */
    public static function loginExists($login, $excludeUserId = null) {
        $conn = new PDO(DB_DSN, DB_USERNAME, DB_PASSWORD);
        $sql = "SELECT COUNT(*) FROM users WHERE login = :login";
        $params = array(':login' => $login);
        
        if ($excludeUserId) {
            $sql .= " AND id != :excludeUserId";
            $params[':excludeUserId'] = $excludeUserId;
        }
        
        $st = $conn->prepare($sql);
        $st->execute($params);
        $count = $st->fetchColumn();
        $conn = null;
        
        return $count > 0;
    }
    
    /**
     * Аутентифицирует пользователя по логину и паролю
     * 
     * @param string $login Логин пользователя
     * @param string $password Пароль пользователя
     * @return User|false Объект пользователя если аутентификация успешна и пользователь активен, иначе false
     */
    public static function authenticate($login, $password) {
        $conn = new PDO(DB_DSN, DB_USERNAME, DB_PASSWORD);
        $sql = "SELECT *, is_active as isActive FROM users WHERE login = :login";
        $st = $conn->prepare($sql);
        $st->bindValue(":login", $login, PDO::PARAM_STR);
        $st->execute();
        
        $row = $st->fetch();
        $conn = null;
        
        if ($row && password_verify($password, $row['password']) && $row['isActive']) {
            return new User($row);
        }
        
        return false;
    }
}