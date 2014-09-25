<div class="alert alert-info text-center">
        XS-Labs is the new home of the XEOS Operating System and related projects.<br />
        Please take a look at the <?php print XS_Menu::getInstance()->getPageLink( '/projects', 'projects page' ); ?> to see a list of all the open projects.
</div>
<h2>Featured projects</h2>
<div class="row">
    <div class="col-md-6">
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="project-icons-monitor-wallpaper">
                    <?php print XS_Menu::getInstance()->getPageLink( '/projects/xeos' ); ?>
                </h3>
            </div>
            <div class="panel-body">
                <a href="<?php print XS_Menu::getInstance()->getPageUrl( '/projects/xeos' ); ?>"><img src="/uploads/image/xeos/icon-small.png" alt="XEOS" width="100" height="100" class="pull-right img-thumbnai" /></a>
                XEOS is an experimental 32/64 bits Operating System for x86 platforms, written from scratch in Assembly and C.<br />
                It includes a C99 Standard Library, and aims at POSIX/SUS2 compatibility.<br />
                Its main purpose is educationnal, and to provide people interested in OS development with a clean code base.<br />
                While available only for x86, it may evolve to support other platforms.<br /><br />
                <div class="text-left">
                    <a class="btn btn-primary btn-sm" href="<?php print XS_Menu::getInstance()->getPageUrl( '/projects/xeos' ); ?>">Learn more...</a>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="project-icons-design-blueprint">
                    <?php print XS_Menu::getInstance()->getPageLink( '/projects/codeine' ); ?>
                </h3>
            </div>
            <div class="panel-body">
                <a href="<?php print XS_Menu::getInstance()->getPageUrl( '/projects/codeine' ); ?>"><img src="/uploads/image/codeine/icon-small.png" alt="Codeine" width="100" height="100" class="pull-right" /></a>
                Codeine is a new code editor for Mac, allowing editing, building, running and debugging C, C++ and Objective-C code.<br />
                While not an IDE (yet), Codeine aims to evolve to support complex application projects.<br />
                Codeine uses the latest technologies in source code compilation to provide users with the best environment to build and run software on the Mac platform.<br /><br />
                <div class="text-left">
                    <a class="btn btn-primary btn-sm" href="<?php print XS_Menu::getInstance()->getPageUrl( '/projects/codeine' ); ?>">Learn more...</a>
                </div>
            </div>
        </div>
    </div>
</div>
