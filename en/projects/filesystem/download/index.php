<div>
    <a href="https://github.com/macmade/FileSystem"><img src="/uploads/image/github.png" alt="GitHub" width="200" height="200" class="img-right" /></a>
</div>
<h2>Downloading FileSystem</h2>
<p>
    FileSystem source code is freely available ont <a href="https://github.com/macmade/FileSystem">GitHub</a>.
</p>
<h2 class="clearer">Latest commits</h2>
<?php
    $GIT = new XS_GitHub_Repository( 'macmade', 'FileSystem' );
    
    print $GIT->commits();
?>
