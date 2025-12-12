<?php include "templates/include/header.php" ?>

<div id="adminHeader">
    <h2>Панель управления администраторм</h2>
    <a href="admin.php?action=newSubcategory">Добавить новую подкатегорию</a> 
</div>

<div id="adminContent">
    <table class="outline">
        <tr>
            <th>Название</th>
            <th>Категория</th>
            <th>Действия</th>
        </tr>

        <?php foreach ($results['subcategories'] as $subcategory) { ?>
            <tr>
                <td><?php echo htmlspecialchars($subcategory->name) ?></td>
                <td><?php echo htmlspecialchars($results['categories'][$subcategory->categoryId]->name) ?></td>
                <td>
                    <a href="admin.php?action=editSubcategory&subcategoryId=<?php echo $subcategory->id ?>">Редактировать</a> |
                    <a href="admin.php?action=deleteSubcategory&subcategoryId=<?php echo $subcategory->id ?>" onclick="return confirm('Удалить эту подкатегорию?')">Удалить</a>
                </td>
            </tr>
        <?php } ?>
    </table>

    <p><?php echo $results['totalRows'] ?> подкатегория(-ий)</p>
    
    <?php if (isset($_GET['status']) && $_GET['status'] == 'changesSaved') { ?>
        <div class="statusMessage">Изменения сохранены</div>
    <?php } ?>
</div>

<?php include "templates/include/footer.php" ?>