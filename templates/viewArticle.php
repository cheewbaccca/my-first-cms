<?php include "templates/include/header.php" ?>
	  
    <h1 style="width: 75%;"><?php echo htmlspecialchars( $results['article']->title )?></h1>
    <div style="width: 75%; font-style: italic;"><?php echo htmlspecialchars( $results['article']->summary )?></div>
    <div style="width: 75%;"><?php echo $results['article']->content?></div>
    <p class="pubDate">Published on <?php echo date('j F Y', $results['article']->publicationDate)?>
    
    <?php if ( $results['category'] ) { ?>
        in 
        <a href="./?action=archive&categoryId=<?php echo $results['category']->id?>">
            <?php echo htmlspecialchars($results['category']->name) ?>
        </a>
    <?php } ?>
    
    <?php if ($results['article']->subcategoryId) { 
        $subcategory = Subcategory::getById($results['article']->subcategoryId); 
        if ($subcategory) { ?>
            | Subcategory: 
            <a href="./?action=archive&subcategoryId=<?php echo $subcategory->id ?>">
                <?php echo htmlspecialchars($subcategory->name) ?>
            </a>
    <?php } } ?>
        
    </p>

    <p><a href="./">Вернуться на главную страницу</a></p>
	  
<?php include "templates/include/footer.php" ?>    