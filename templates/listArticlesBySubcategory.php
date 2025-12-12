<?php include "templates/include/header.php" ?>

<h1>Articles in Subcategory: <?php echo htmlspecialchars($results['subcategory']->name) ?></h1>

<?php foreach ($results['articles'] as $article) { ?>
    <div class="newsitem">
        <h2><?php echo htmlspecialchars($article->title) ?></h2>
        <div class="newsitem_date"><?php echo date('j F Y', $article->publicationDate) ?></div>
        <div class="newsitem_summary"><?php echo htmlspecialchars($article->summary) ?></div>
        <div class="newsitem_category">
            Category: <a href="./?action=archive&categoryId=<?php echo $article->categoryId ?>"><?php echo htmlspecialchars($results['categories'][$article->categoryId]->name) ?></a>
            <?php if ($article->subcategoryId && isset($results['subcategories'][$article->subcategoryId])) { ?>
                | Subcategory: <a href="./?action=archive&subcategoryId=<?php echo $article->subcategoryId ?>"><?php echo htmlspecialchars($results['subcategories'][$article->subcategoryId]->name) ?></a>
            <?php } ?>
        </div>
        <div class="newsitem_link"><a href="./?action=viewArticle&articleId=<?php echo $article->id?>">Read article</a></div>
    </div>
<?php } ?>

<?php if (isset($results['prevPage']) || isset($results['nextPage'])) { ?>
    <div class="pager">
        <?php if (isset($results['prevPage'])) { ?>
            <a href="./?action=archive&subcategoryId=<?php echo $results['subcategory']->id ?>&page=<?php echo $results['prevPage'] ?>">Prev</a> 
        <?php } ?>
        
        <?php if (isset($results['nextPage'])) { ?>
            <a href="./?action=archive&subcategoryId=<?php echo $results['subcategory']->id ?>&page=<?php echo $results['nextPage'] ?>">Next</a>
        <?php } ?>
    </div>
<?php } ?>

<?php include "templates/include/footer.php" ?>