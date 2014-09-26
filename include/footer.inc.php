        </div>
    </div>
    <div class="footer">
        <div class="container">
            <div class="pull-left">
                <small>XS-Labs &copy; <?php print date( 'Y', time() ); ?> - All Rights Reserved - All Wrongs Reserved</small>
            </div>
            <div class="pull-right">
                <ul class="list-inline">
                    <li><small><?php print XS_Menu::getInstance()->getPageLink( '/legal/privacy/' ); ?></small></li>
                    <li><small><?php print XS_Menu::getInstance()->getPageLink( '/legal/credits/' ); ?></small></li>
                </ul>
            </div>
        </div>
    </div>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js" type="text/javascript"></script>
    <script src="/bootstrap/js/bootstrap.min.js" type="text/javascript"></script>
    <script src="/js/imagelightbox.min.js" type="text/javascript"></script>
    <script type="text/javascript">
        // <![CDATA[
        
        $( document ).ready
        (
            function()
            {
                $( 'a.lightbox' ).imageLightbox();
            }
        );
        
        // ]]>
    </script>
    <script src="/js/highlight.pack.js" type="text/javascript"></script>
    <script type="text/javascript">
        // <![CDATA[
        
        $( document ).ready
        (
            function()
            {
                $( 'div.code-block' ).each
                (
                    function( i, block )
                    {
                        hljs.highlightBlock( block );
                    }
                );
            }
        );
        
        // ]]>
    </script>
</body>
</html>
