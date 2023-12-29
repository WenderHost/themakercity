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
    return '<style>
/*! elementor - v3.7.4 - 31-08-2022 */
.elementor-alert{padding:15px;border-left:5px solid transparent;position:relative;text-align:left}.elementor-alert .elementor-alert-title{display:block;font-weight:700}.elementor-alert .elementor-alert-description{font-size:16px}.elementor-alert button.elementor-alert-dismiss{position:absolute;right:var(--dismiss-icon-horizontal-position,10px);top:var(--dismiss-icon-vertical-position,10px);padding:3px;font-size:var(--dismiss-icon-size,20px);line-height:1;background:transparent;color:var(--dismiss-icon-normal-color,inherit);border:none;cursor:pointer;-webkit-transition-duration:var(--dismiss-icon-hover-transition-duration,.3s);-o-transition-duration:var(--dismiss-icon-hover-transition-duration,.3s);transition-duration:var(--dismiss-icon-hover-transition-duration,.3s)}.elementor-alert button.elementor-alert-dismiss:hover{color:var(--dismiss-icon-hover-color,inherit)}.elementor-alert button.elementor-alert-dismiss svg{width:var(--dismiss-icon-size,20px);height:var(--dismiss-icon-size,20px);fill:var(--dismiss-icon-normal-color,currentColor);-webkit-transition-duration:var(--dismiss-icon-hover-transition-duration,.3s);-o-transition-duration:var(--dismiss-icon-hover-transition-duration,.3s);transition-duration:var(--dismiss-icon-hover-transition-duration,.3s)}.elementor-alert button.elementor-alert-dismiss svg:hover{fill:var(--dismiss-icon-hover-color,currentColor)}.elementor-alert.elementor-alert-info{color:#31708f;background-color:#d9edf7;border-color:#bcdff1}.elementor-alert.elementor-alert-success{color:#3c763d;background-color:#dff0d8;border-color:#cae6be}.elementor-alert.elementor-alert-warning{color:#8a6d3b;background-color:#fcf8e3;border-color:#f9f0c3}.elementor-alert.elementor-alert-danger{color:#a94442;background-color:#f2dede;border-color:#e8c4c4}@media (max-width:767px){.elementor-alert{padding:10px}.elementor-alert button.elementor-alert-dismiss{right:7px;top:7px}}
.elementor-alert p:last-child{margin-bottom: 0;}.elementor-alert.large-title .elementor-alert-title{font-size: 1.5em;}.elementor-alert-hidden{display: none;}
</style>
<div class="elementor-alert elementor-widget elementor-alert-'.htmlspecialchars((string)(($inary && isset($in['type'])) ? $in['type'] : null), ENT_QUOTES, 'UTF-8').' '.htmlspecialchars((string)(($inary && isset($in['css_classes'])) ? $in['css_classes'] : null), ENT_QUOTES, 'UTF-8').'" role="alert">
    '.((LR::ifvar($cx, (($inary && isset($in['title'])) ? $in['title'] : null), false)) ? '<span class="elementor-alert-title">'.htmlspecialchars((string)(($inary && isset($in['title'])) ? $in['title'] : null), ENT_QUOTES, 'UTF-8').'</span>' : '').'
    <span class="elementor-alert-description">'.(($inary && isset($in['description'])) ? $in['description'] : null).'</span>
'.((LR::ifvar($cx, (($inary && isset($in['dismissable'])) ? $in['dismissable'] : null), false)) ? '    <button type="button" class="elementor-alert-dismiss"><span aria-hidden="true">×</span><span class="elementor-screen-only">Dismiss alert</span></button>
' : '').'</div>';
};
?>