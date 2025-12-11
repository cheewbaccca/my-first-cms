<?php include "templates/include/header.php" ?>

<div id="adminHeader">
    <h2>Панель управления администраторм</h2>
    <a href="admin.php?action=newUser">Добавить нового пользователя</a> 
</div>

<div id="adminContent">
    <table class="outline">
        <tr>
            <th>Логин</th>
            <th>Активность</th>
            <th>Дата создания</th>
            <th>Действия</th>
        </tr>

        <?php foreach ($results['users'] as $user) { ?>
            <tr>
                <td><?php echo htmlspecialchars($user->login ?? '') ?></td>
                <td><?php echo ($user->isActive ?? 0) ? 'Да' : 'Нет' ?></td>
                <td><?php echo date('Y-m-d H:i:s', $user->id ? strtotime('+' . $user->id . ' seconds', strtotime('2020-01-01')) : time()) ?></td>
                <td>
                    <a href="admin.php?action=editUser&userId=<?php echo (int)$user->id ?>">Редактировать</a> |
                    <a href="admin.php?action=deleteUser&userId=<?php echo (int)$user->id ?>" onclick="return confirm('Удалить этого пользователя?')">Удалить</a>
                </td>
            </tr>
        <?php } ?>
    </table>

    <p><?php echo $results['totalRows'] ?> пользователь(-я, -ей)</p>
    
    <?php if (isset($_GET['status']) && $_GET['status'] == 'changesSaved') { ?>
        <div class="statusMessage">Изменения сохранены</div>
    <?php } ?>
</div>

<?php include "templates/include/footer.php" ?>