<div>
    <a href="https://github.com/macmade/XEOS"><img src="/uploads/image/github.png" alt="GitHub" width="200" height="200" class="img-right" /></a>
</div>
<h2>Downloading XEOS</h2>
<p>
    XEOS source code is freely available ont <a href="https://github.com/macmade/XEOS">GitHub</a>.<br />
    Follow the instructions in the <?php print XS_Menu::getInstance()->getPageLink( '/projects/xeos/documentation', 'documentation' ); ?> to build and use it.
</p>
<h2 class="clearer">Latest commits</h2>
<?php
    $GIT = new XS_GitHub_Repository( 'macmade', 'XEOS' );
    
    print $GIT->commits();
?>

