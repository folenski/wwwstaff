<?php use \LightnCandy\Runtime as LR;return function ($in = null, $options = null) {
    $helpers = array();
    $partials = array();
    $cx = array(
        'flags' => array(
            'jstrue' => false,
            'jsobj' => false,
            'jslen' => false,
            'spvar' => false,
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
    return '<div class="container-fluid">
    <a class="navbar-brand" href="#">Staff-site demo</a>
  <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNavDropdown" aria-controls="navbarNavDropdown" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
  </button>
<div class="collapse navbar-collapse" id="navbarNavDropdown">
  <ul class="navbar-nav">
'.LR::sec($cx, (($inary && isset($in['nav'])) ? $in['nav'] : null), null, $in, false, function($cx, $in) {$inary=is_array($in);return ''.((LR::isec($cx, (($inary && isset($in['down'])) ? $in['down'] : null))) ? '     <li class="nav-item"><a class="nav-link '.(($inary && isset($in['active'])) ? $in['active'] : null).'" href="'.(($inary && isset($in['uri'])) ? $in['uri'] : null).'">'.(($inary && isset($in['name'])) ? $in['name'] : null).'</a></li>
' : '').''.LR::sec($cx, (($inary && isset($in['down'])) ? $in['down'] : null), null, $in, false, function($cx, $in) {$inary=is_array($in);return '      <li class="nav-item dropdown"><a class="nav-link dropdown-toggle '.(($inary && isset($in['active'])) ? $in['active'] : null).'" href="'.(($inary && isset($in['uri'])) ? $in['uri'] : null).'"
         id="navbarDropdownMenuLink" role="button" data-bs-toggle="dropdown" aria-expanded="false">'.(($inary && isset($in['name'])) ? $in['name'] : null).'</a>
      <ul class="dropdown-menu" aria-labelledby="navbarDropdownMenuLink">
'.LR::sec($cx, (($inary && isset($in['dropdown'])) ? $in['dropdown'] : null), null, $in, false, function($cx, $in) {$inary=is_array($in);return '        <li><a class="dropdown-item" href="'.(($inary && isset($in['uri'])) ? $in['uri'] : null).'">'.(($inary && isset($in['name'])) ? $in['name'] : null).'</a></li>
';}).'      </ul></li>
';}).'';}).'  </ul>
</div>
</div>';
}; ?>