<h1 class="rmc_titles"><?php _e('Dashboard','rmcommon'); ?></h1>

<div class="row-fluid">

    <div class="span5">

        <!-- System tools -->
        <div class="outer box-collapse">
            <div class="th">
                <i class="icon-caret-up control"></i>
                <?php _e('System Tools','rmcommon'); ?>
            </div>
            <div class="even system_tools collapsable">
                <div class="row-fluid">
                    <div class="span6">
                        <a style="background-image: url(images/configure.png);" href="<?php echo XOOPS_URL; ?>/modules/system/admin.php?fct=preferences&op=showmod&mod=<?php echo $xoopsModule->mid(); ?>"><?php _e('Configure Common Utilities','rmcommon'); ?></a>
                    </div>
                    <div class="span6">
                        <a style="background-image: url(images/images.png);" href="images.php"><?php _e('Images Manager','rmcommon'); ?></a>
                    </div>
                </div>

                <div class="row-fluid">
                    <div class="span6">
                        <a style="background-image: url(images/comments.png);" href="comments.php"><?php _e('Comments Management','rmcommon'); ?></a>
                    </div>
                    <div class="span6">
                        <a style="background-image: url(images/plugin.png);" href="plugins.php"><?php _e('Plugins Management','rmcommon'); ?></a>
                    </div>
                </div>

                <div class="row-fluid">
                    <div class="span6">
                        <a style="background-image: url(images/modules.png);" href="modules.php"><?php _e('XOOPS Modules','rmcommon'); ?></a>
                    </div>
                    <div class="span6">
                        <a style="background-image: url(images/users.png);" href="users.php"><?php _e('Users Management','rmcommon'); ?></a>
                    </div>
                </div>

                <?php
                $system_tools = RMEvents::get()->run_event('rmcommon.get.system.tools', array());
                $i = 1;
                ?>
                <?php if($system_tools): ?>
                    <div class="row-fluid">
                    <?php foreach ($system_tools as $tool): ?>
                        <?php if($i>2): ?>
                            </div><div class="row-fluid">
                            <?php $i=1; ?>
                        <?php endif; ?>
                        <div class="span6"><a href="<?php echo $tool['link']; ?>" style="background-image: url(<?php echo $tool['icon']; ?>);"><?php echo $tool['caption']; ?></a></div>
                        <?php $i++; endforeach; ?>
                    </div>
                <?php endif; ?>

            </div>
        </div>
        <!--// End system tools -->

        <!-- INstalled Modules -->
        <div class="outer box-collapse">
            <div class="th">
                <img src="images/loading_2.gif" alt="" class="loading" id="loading-mods" />
                <i class="icon-caret-up control"></i>
                <?php _e('Installed Modules','rmcommon'); ?>
            </div>
            <div class="even mods_coint collapsable">
                <div id="ajax-mods-list">

                </div>
                <span class="description">
                    <?php _e('If you wish to manage or install new modules please go to Modules Management.','rmcommon'); ?><br />
                    <a href="<?php echo XOOPS_URL; ?>/modules/system/admin.php?fct=modulesadmin"><?php _e('Modules management', 'rmcommon'); ?></a>
                </span>
            </div>
        </div>


    </div>

    <div class="span4">

        <!-- UPdates available -->
        <div class="alert alert-block" style="display: none;" id="updater-info">
            <h4><?php echo sprintf(__('%s Updates Available!','rmcommon'), '<span class="badge badge-important">%s</span>'); ?></h4>
            <p><?php echo sprintf(__('Please %s to view available updates.','rmcommon'), '<a href="updates.php">'.__('click here','rmcommon').'</a>'); ?></p>
        </div>

        <!-- Support me -->
        <div class="outer">
            <div class="th"><i class="icon-thumbs-up"></i> <?php _e('Support my Work','rmcommon'); ?></div>
            <div class="even support-me">
                <img class="avatar" src="http://www.gravatar.com/avatar/a888698732624c0a1d4da48f1e5c6bb4?s=80" alt="Eduardo Cortés (bitcero)" />
                <p><?php _e('Do you like my work? Then maybe you want support me to continue developing new modules.','rmcommon'); ?></p>
                <?php echo $donateButton; ?>
            </div>
        </div>
        <!--// End support me -->

        <!-- Available Modules -->
        <div class="outer box-collapse">
            <div class="th">
                <i class="icon-caret-up control"></i>
                <?php _e('Available Modules','rmcommon'); ?>
            </div>
            <div class="collapsable">
                <?php foreach($available_mods as $module): ?>
                    <div class="<?php echo tpl_cycle("even,odd"); ?>">
                        <span class="modimg" style="background: url(../<?php echo $module->getInfo('dirname'); ?>/<?php echo $module->getInfo('icon48')!='' ? $module->getInfo('icon48') : $module->getInfo('image'); ?>) no-repeat center;">&nbsp;</span>
                        <strong><?php echo $module->getInfo('name'); ?></strong><br />
                        <span class="moddesc"><?php echo $module->getInfo('description'); ?></span><br />
                        <a href="modules.php?action=install&dir=<?php echo $module->getInfo('dirname'); ?>"><?php _e('Install', 'rmcommon'); ?></a>
                    </div>
                <?php endforeach; ?>
                <span class="description">
	                <?php _e('If you wish to manage or install new modules please go to Modules Management.','rmcommon'); ?><br />
	                <a href="modules.php"><?php _e('Modules management', 'rmcommon'); ?></a>
	            </span>
            </div>
        </div>
        <!-- End available modules -->

    </div>

    <div class="span3">

        <!-- Recent News -->
        <div class="outer box-collapse">
            <div class="th">
                <i class="icon-caret-up control"></i>
                <img src="images/loading_2.gif" alt="" class="loading" id="loading-news" />
                <?php _e('Recent News','rmcommon'); ?>
            </div>
            <div class="even collapsable" id="rmc-recent-news">

            </div>
        </div>
        <!--// End recent news -->

    </div>

</div>

<div class="row-fluid rmcw-container">
    <!-- Left widgets -->
    <div class="span6">


        <!-- Recent news -->

        <!-- End recent news -->

        <?php RMEvents::get()->run_event('rmcommon.dashboard.left.widgets'); ?>

    </div>
    <!-- End left widgets -->

    <!-- Right widgets -->
    <div class="span6" id="rmc-central-right-widgets">






        <?php RMEvents::get()->run_event('rmcommon.dashboard.right.widgets'); ?>
    </div>
    <!-- / End right widgets -->
</div>