<?php include "templates/include/header.php" ?>

<form action="admin.php?action=<?php echo $results['formAction'] ?>&subcategoryId=<?php echo $results['subcategory']->id ?>" method="post">
    <input type="hidden" name="subcategoryId" value="<?php echo (int)$results['subcategory']->id ?>"/>
    
    <?php if (isset($results['errorMessage'])) { ?>
        <div class="errorMessage"><?php echo htmlspecialchars($results['errorMessage']) ?></div>
    <?php } ?>
    
    <ul>

        <li>
            <label for="name">Название подкатегории</label>
            <input type="text" name="name" id="name" placeholder="Введите название подкатегории" required autofocus maxlength="255" value="<?php echo htmlspecialchars($results['subcategory']->name ?? '') ?>"/>
        </li>

        <li>
            <label for="categoryId">Категория</label>
            <select name="categoryId" id="categoryId" required>
                <option value="">Выберите категорию</option>
                <?php foreach ($results['categories'] as $category) { ?>
                    <option value="<?php echo $category->id ?>" <?php echo $category->id == $results['subcategory']->categoryId ? 'selected' : '' ?>><?php echo htmlspecialchars($category->name) ?></option>
                <?php } ?>
            </select>
        </li>

    </ul>

    <div class="buttons">
        <input type="submit" name="saveChanges" value="Сохранить"/>
        <input type="submit" name="cancel" value="Отмена"/>
    </div>

</form>

<?php include "templates/include/footer.php" ?>