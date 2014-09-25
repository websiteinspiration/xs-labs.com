<div>
    <a href="https://github.com/macmade/XEOS-Software-License"><img src="/uploads/image/github.png" alt="GitHub" width="200" height="200" class="img-right" /></a>
</div>
<h2>Contributing to the XEOS Software License</h2>
<p>
    The project is hosted on <a href="https://github.com/macmade/XEOS-Software-License">GitHub</a>.<br />
    Feel free to comment, request modifications, etc.
</p>
<h2 class="clearer">Latest commits</h2>
<?php
    $GIT = new XS_GitHub_Repository( 'macmade', 'XEOS-Software-License' );
    
    print $GIT->commits();
?>
