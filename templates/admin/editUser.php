<?php include "templates/include/header.php" ?>

<form action="admin.php?action=<?php echo htmlspecialchars($results['formAction'] ?? '') ?>&userId=<?php echo (int)$results['user']->id ?>" method="post">
    <input type="hidden" name="userId" value="<?php echo (int)$results['user']->id ?>"/>
    
    <?php if (isset($results['errorMessage'])) { ?>
        <div class="errorMessage"><?php echo $results['errorMessage'] ?></div>
    <?php } ?>
    
    <ul>

        <li>
            <label for="login">Логин</label>
            <input type="text" name="login" id="login" placeholder="Введите логин пользователя" required autofocus maxlength="20" value="<?php echo htmlspecialchars($results['user']->login ?? '') ?>"/>
        </li>

        <li>
            <label for="password">Пароль</label>
            <input type="password" name="password" id="password" placeholder="Введите пароль пользователя" maxlength="20" value=""/>
            <span>(Оставьте пустым, чтобы не изменять)</span>
        </li>

        <li>
            <label for="isActive">Активен</label>
            <input type="checkbox" name="isActive" id="isActive" value="1" <?php echo ($results['user']->isActive ?? 0) ? 'checked' : '' ?>/>
        </li>

    </ul>

    <div class="buttons">
        <input type="submit" name="saveChanges" value="Сохранить"/>
        <input type="submit" name="cancel" value="Отмена"/>
    </div>

</form>

<?php include "templates/include/footer.php" ?>