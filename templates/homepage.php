<?php include "templates/include/header.php" ?>
    <ul id="headlines">
    <?php foreach ($results['articles'] as $article) { ?>
        <li class='<?php echo $article->id?>'>
            <h2>
                <span class="pubDate">
                    <?php echo date('j F', $article->publicationDate)?>
                </span>
                
                <a href=".?action=viewArticle&articleId=<?php echo $article->id?>">
                    <?php echo htmlspecialchars( $article->title )?>
                </a>
                
                <?php if (isset($article->categoryId) && $article->categoryId) { ?>
                    <span class="category">
                        in 
                        <a href=".?action=archive&categoryId=<?php echo $article->categoryId?>">
                            <?php echo htmlspecialchars($results['categories'][$article->categoryId]->name )?>
                        </a>
                    </span>
                <?php } else { ?>
                    <span class="category">
                        <?php echo "Без категории"?>
                    </span>
                <?php } ?>
                
                <?php if ($article->subcategoryId && isset($results['subcategories'][$article->subcategoryId])) { ?>
                    <span class="subcategory">
                        | in
                        <a href=".?action=archive&subcategoryId=<?php echo $article->subcategoryId ?>">
                            <?php echo htmlspecialchars($results['subcategories'][$article->subcategoryId]->name) ?>
                        </a>
                    </span>
                <?php } ?>
            </h2>
            <p class="summary">
                <?php 

                $shortContent = mb_substr($article->content, 0, 50, 'UTF-8');
                if (mb_strlen($article->content, 'UTF-8') > 50) {
                    $shortContent .= '...';
                }
                echo htmlspecialchars($shortContent);
                ?>
            </p>
            <img id="loader-identity" src="JS/ajax-loader.gif" alt="gif">
            
            <ul class="ajax-load">
                <li><a href=".?action=viewArticle&articleId=<?php echo $article->id?>" class="ajaxArticleBodyByPost" data-contentId="<?php echo $article->id?>">Показать продолжение (POST)</a></li>
                <li><a href=".?action=viewArticle&articleId=<?php echo $article->id?>" class="ajaxArticleBodyByGet" data-contentId="<?php echo $article->id?>">Показать продолжение (GET)</a></li>
                <li><a href=".?action=viewArticle&articleId=<?php echo $article->id?>" class="">(POST) -- NEW</a></li>
                <li><a href=".?action=viewArticle&articleId=<?php echo $article->id?>" class="">(GET)  -- NEW</a></li>
            </ul>
            <a href=".?action=viewArticle&articleId=<?php echo $article->id?>" class="showContent" data-contentId="<?php echo $article->id?>">Показать полностью</a>
        </li>
    <?php } ?>
    </ul>
    <p><a href="./?action=archive">Article Archive</a></p>
<?php include "templates/include/footer.php" ?>

