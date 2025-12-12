<?php

/**
 * Класс для обработки подкатегорий
 */
class Subcategory
{
    // Свойства
    /**
    * @var int ID подкатегории из базы данных
    */
    public $id = null;

    /**
    * @var string Название подкатегории
    */
    public $name = null;

    /**
    * @var int ID категории, к которой относится подкатегория
    */
    public $categoryId = null;

    /**
     * Создаст объект подкатегории
     * 
     * @param array $data массив значений (столбцов) строки таблицы подкатегорий
     */
    public function __construct($data=array())
    {
        if (isset($data['id'])) {
            $this->id = (int) $data['id'];
        }
        
        if (isset($data['name'])) {
            $this->name = $data['name'];        
        }
        
        if (isset($data['categoryId'])) {
            $this->categoryId = (int) $data['categoryId'];      
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
    }


    /**
    * Возвращаем объект подкатегории соответствующий заданному ID
    *
    * @param int ID подкатегории
    * @return Subcategory|false Объект подкатегории или false, если запись не найдена или возникли проблемы
    */
    public static function getById($id) {
        $conn = new PDO(DB_DSN, DB_USERNAME, DB_PASSWORD);
        $sql = "SELECT * FROM subcategories WHERE id = :id";
        $st = $conn->prepare($sql);
        $st->bindValue(":id", $id, PDO::PARAM_INT);
        $st->execute();

        $row = $st->fetch();
        $conn = null;
        
        if ($row) { 
            return new Subcategory($row);
        }
        return false;
    }

    /**
    * Возвращает все (или диапазон) объекты Subcategory из базы данных
    *
    * @param int $numRows Количество возвращаемых строк (по умолчанию = 1000000)
    * @param int $categoryId Вернуть подкатегории только из категории с указанным ID
    * @param string $order Столбец, по которому выполняется сортировка подкатегорий (по умолчанию = "name ASC")
    * @return Array|false Двух элементный массив: results => массив объектов Subcategory; totalRows => общее количество строк
    */
    public static function getList($numRows=1000, $categoryId=null, $order="name ASC") 
    {
        $conn = new PDO(DB_DSN, DB_USERNAME, DB_PASSWORD);
        
        $categoryClause = '';
        $params = array();
        
        if($categoryId) {
            $categoryClause = "WHERE categoryId = :categoryId";                 
            $params[':categoryId'] = $categoryId;
        }

        $sql = "SELECT SQL_CALC_FOUND_ROWS * 
                FROM subcategories 
                $categoryClause
                ORDER BY $order LIMIT :numRows";
        
        $st = $conn->prepare($sql);
        $st->bindValue(":numRows", $numRows, PDO::PARAM_INT);
        
        foreach($params as $paramName => $paramValue) {
            $st->bindValue($paramName, $paramValue, PDO::PARAM_INT);
        }
        
        $st->execute();
        $list = array();

        while ($row = $st->fetch()) {
            $subcategory = new Subcategory($row);
            $list[] = $subcategory;
        }

        // Получаем общее количество подкатегорий, которые соответствуют критерию
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
    * Вставляем текущий объект Subcategory в базу данных, устанавливаем его ID.
    */
    public function insert() {

        // Есть уже у объекта Subcategory ID?
        if (!is_null($this->id)) trigger_error("Subcategory::insert(): Attempt to insert an Subcategory object that already has its ID property set (to $this->id).", E_USER_ERROR);

        // Вставляем подкатегорию
        $conn = new PDO(DB_DSN, DB_USERNAME, DB_PASSWORD);
        $sql = "INSERT INTO subcategories (name, categoryId) VALUES (:name, :categoryId)";
        $st = $conn->prepare($sql);
        $st->bindValue(":name", $this->name, PDO::PARAM_STR);
        $st->bindValue(":categoryId", $this->categoryId, PDO::PARAM_INT);
        $st->execute();
        $this->id = $conn->lastInsertId();
        $conn = null;
    }

    /**
    * Обновляем текущий объект подкатегории в базе данных
    */
    public function update() {

      // Есть ли у объекта подкатегории ID?
      if (is_null($this->id)) trigger_error("Subcategory::update(): Attempt to update an Subcategory object that does not have its ID property set.", E_USER_ERROR);

      // Обновляем подкатегорию
      $conn = new PDO(DB_DSN, DB_USERNAME, DB_PASSWORD);
      $sql = "UPDATE subcategories SET name=:name, categoryId=:categoryId WHERE id = :id";
      $st = $conn->prepare($sql);
      $st->bindValue(":name", $this->name, PDO::PARAM_STR);
      $st->bindValue(":categoryId", $this->categoryId, PDO::PARAM_INT);
      $st->bindValue(":id", $this->id, PDO::PARAM_INT);
      $st->execute();
      $conn = null;
    }


    /**
    * Удаляем текущий объект подкатегории из базы данных
    */
    public function delete() {

      // Есть ли у объекта подкатегории ID?
      if (is_null($this->id)) trigger_error("Subcategory::delete(): Attempt to delete an Subcategory object that does not have its ID property set.", E_USER_ERROR);

      // Обновляем статьи, которые ссылаются на эту подкатегорию, устанавливая subcategoryId в NULL
      $conn = new PDO(DB_DSN, DB_USERNAME, DB_PASSWORD);
      $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // Включаем режим исключений
      
      try {
          $conn->beginTransaction();
          
          // Сначала обновляем все статьи, чтобы не ссылаться на эту подкатегорию
          $updateSql = "UPDATE articles SET subcategoryId = NULL WHERE subcategoryId = :id";
          $updateSt = $conn->prepare($updateSql);
          $updateSt->bindValue(":id", $this->id, PDO::PARAM_INT);
          $updateSt->execute();
          
          // Затем удаляем подкатегорию
          $deleteSql = "DELETE FROM subcategories WHERE id = :id LIMIT 1";
          $deleteSt = $conn->prepare($deleteSql);
          $deleteSt->bindValue(":id", $this->id, PDO::PARAM_INT);
          $deleteSt->execute();
          
          $conn->commit();
      } catch (Exception $e) {
          $conn->rollback();
          throw $e;
      }
      
      $conn = null;
    }
    
    /**
     * Проверяет, существует ли подкатегория с указанным именем в заданной категории
     * 
     * @param string $name Имя подкатегории для проверки
     * @param int $categoryId ID категории
     * @param int $excludeSubcategoryId ID подкатегории для исключения из проверки (при редактировании)
     * @return bool true если имя подкатегории уже существует в этой категории, иначе false
     */
    public static function nameExistsInCategory($name, $categoryId, $excludeSubcategoryId = null) {
        $conn = new PDO(DB_DSN, DB_USERNAME, DB_PASSWORD);
        $sql = "SELECT COUNT(*) FROM subcategories WHERE name = :name AND categoryId = :categoryId";
        $params = array(
            ':name' => $name,
            ':categoryId' => $categoryId
        );
        
        if ($excludeSubcategoryId) {
            $sql .= " AND id != :excludeSubcategoryId";
            $params[':excludeSubcategoryId'] = $excludeSubcategoryId;
        }
        
        $st = $conn->prepare($sql);
        $st->execute($params);
        $count = $st->fetchColumn();
        $conn = null;
        
        return $count > 0;
    }
    
    /**
     * Возвращает все подкатегории, сгруппированные по категориям
     * 
     * @return array Массив категорий, в которых содержатся подкатегории
     */
    public static function getListGroupedByCategory() {
        $conn = new PDO(DB_DSN, DB_USERNAME, DB_PASSWORD);
        
        $sql = "SELECT s.*, c.name as categoryName 
                FROM subcategories s 
                JOIN categories c ON s.categoryId = c.id 
                ORDER BY c.name, s.name";
        
        $st = $conn->prepare($sql);
        $st->execute();
        
        $groupedSubcategories = array();
        
        while ($row = $st->fetch()) {
            $categoryName = $row['categoryName'];
            if (!isset($groupedSubcategories[$categoryName])) {
                $groupedSubcategories[$categoryName] = array();
            }
            $groupedSubcategories[$categoryName][] = new Subcategory($row);
        }
        
        $conn = null;
        return $groupedSubcategories;
    }
    
    /**
     * Проверяет, принадлежит ли подкатегория к указанной категории
     *
     * @param int $subcategoryId ID подкатегории
     * @param int $categoryId ID категории
     * @return bool true если подкатегория принадлежит категории, иначе false
     */
    public static function belongsToCategory($subcategoryId, $categoryId) {
        $subcategory = self::getById($subcategoryId);
        return $subcategory && $subcategory->categoryId == $categoryId;
    }
}