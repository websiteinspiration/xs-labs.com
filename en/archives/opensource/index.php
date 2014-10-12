<div>
    <img src="/uploads/image/archives-opensource/icon-circle.png" width="140" height="140" class="pull-right" />
</div>
<p>
    Here's a non-exhaustive list of past OpenSource projects, which are no longer active for the moment.<br />
    Most of them were actually developed under the <a href="http://www.eosgarden.com/en/opensource/">eosgarden</a> identity, and are listed here for archive purpose.
</p>
<p>
    Please take a look at the <?php print \XS\Menu::getInstance()->getPageLink( '/projects', 'projects page' ); ?> to see the list of the active OpenSource projects by XS-Labs.
</p>
<h3 class="clearer">Past projects</h3>
<?php
    print new \XS\GitHub\Repository\Listing
    (
        'macmade',
        array
        (
            'XEOS',
            'XSFoundation',
            'Codeine',
            'ClangKit',
            'FileSystem',
            'XEOS-Software-License',
            'buddy',
            'acpica'
        )
    );
?>
