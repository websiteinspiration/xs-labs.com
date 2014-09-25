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
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
    <script src="/bootstrap/js/bootstrap.min.js"></script>
    <script src="/js/shadowbox.js" type="text/javascript"></script>
    <script type="text/javascript">
        // <![CDATA[
        $( document ).ready
        (
            function()
            {
                Shadowbox.init
                (
                    {
                        overlayOpacity: 0.9
                    }
                );
            }
        );
        // ]]>
    </script>
</body>
</html>
