<div>
    <a href="https://github.com/macmade/FileSystem"><img src="/uploads/image/github.png" alt="GitHub" width="200" height="200" class="pull-right" /></a>
</div>
<p>
    FileSystem source code is freely available ont <a href="https://github.com/macmade/FileSystem">GitHub</a>.
</p>
<h3 class="clearer">Latest commits</h3>
<?php
    $GIT = new XS_GitHub_Repository( 'macmade', 'FileSystem' );
    
    print $GIT->commits();
?>
