<?php

defined('C5_EXECUTE') or die('Access Denied.');

use Concrete\Core\Support\Facade\Url;

?>

<a  class="btn btn-secondary" href="javascript:void(0);" id="ccm-report-bug">
    <?php echo t('Get Help') ?>
</a>

<script>
    (function ($) {
        $("#ccm-report-bug").click(function () {
            jQuery.fn.dialog.open({
                href: "<?php echo (string)Url::to("/ccm/system/dialogs/simple_configurator/create_ticket"); ?>",
                modal: true,
                width: 500,
                title: "<?php echo h(t("Support"));?>",
                height: '80%'
            });
        });
    })(jQuery);
</script>