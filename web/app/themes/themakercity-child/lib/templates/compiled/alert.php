<?php
use \LightnCandy\SafeString as SafeString;use \LightnCandy\Runtime as LR;return function ($in = null, $options = null) {
    $helpers = array();
    $partials = array();
    $cx = array(
        'flags' => array(
            'jstrue' => false,
            'jsobj' => false,
            'jslen' => false,
            'spvar' => true,
            'prop' => false,
            'method' => false,
            'lambda' => false,
            'mustlok' => false,
            'mustlam' => false,
            'mustsec' => false,
            'echo' => false,
            'partnc' => false,
            'knohlp' => false,
            'debug' => isset($options['debug']) ? $options['debug'] : 1,
        ),
        'constants' => array(),
        'helpers' => isset($options['helpers']) ? array_merge($helpers, $options['helpers']) : $helpers,
        'partials' => isset($options['partials']) ? array_merge($partials, $options['partials']) : $partials,
        'scopes' => array(),
        'sp_vars' => isset($options['data']) ? array_merge(array('root' => $in), $options['data']) : array('root' => $in),
        'blparam' => array(),
        'partialid' => 0,
        'runtime' => '\LightnCandy\Runtime',
    );
    
    $inary=is_array($in);
    return '
<div class="elementor-alert elementor-widget elementor-alert-'.htmlspecialchars((string)(($inary && isset($in['type'])) ? $in['type'] : null), ENT_QUOTES, 'UTF-8').' '.htmlspecialchars((string)(($inary && isset($in['css_classes'])) ? $in['css_classes'] : null), ENT_QUOTES, 'UTF-8').'" role="alert">
    '.((LR::ifvar($cx, (($inary && isset($in['title'])) ? $in['title'] : null), false)) ? '<span class="elementor-alert-title">'.htmlspecialchars((string)(($inary && isset($in['title'])) ? $in['title'] : null), ENT_QUOTES, 'UTF-8').'</span>' : '').'
    <span class="elementor-alert-description">'.(($inary && isset($in['description'])) ? $in['description'] : null).'</span>
'.((LR::ifvar($cx, (($inary && isset($in['dismissable'])) ? $in['dismissable'] : null), false)) ? '    <button type="button" class="elementor-alert-dismiss"><span aria-hidden="true">×</span><span class="elementor-screen-only">Dismiss alert</span></button>
' : '').'</div>';
};
?>