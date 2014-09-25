<h2>About</h2>
<p>
    Here's a non-exhaustive list of past OpenSource projects, which are no longer active for the moment.<br />
    Most of them were actually developed under the <a href="http://www.eosgarden.com/en/opensource/">eosgarden</a> identity, and are listed here for archive purpose.
</p>
<p>
    Please take a look at the <?php print XS_Menu::getInstance()->getPageLink( '/projects', 'projects page' ); ?> to see the list of the active OpenSource projects by XS-Labs.
</p>
<h2>Past projects</h2>
<?php
    print new XS_GitHub_Repository_List
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
