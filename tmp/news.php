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
    return ''.LR::sec($cx, (($inary && isset($in['content'])) ? $in['content'] : null), null, $in, false, function($cx, $in) {$inary=is_array($in);return '<div class="row shadow-sm p-3 my-3 bg-body rounded">
  <div class="col-4">
    <img
      class="img-fluid"
      src="'.(($inary && isset($in['img_src'])) ? $in['img_src'] : null).'"
      alt="'.(($inary && isset($in['img_alt'])) ? $in['img_alt'] : null).'"
      data-reveal="display"
    />
  </div>
  <div class="col-8">
    <h3>'.(($inary && isset($in['title'])) ? $in['title'] : null).'</h3>
    <hr />
    <h4>'.(($inary && isset($in['date'])) ? $in['date'] : null).'</h4>
    '.(($inary && isset($in['article'])) ? $in['article'] : null).'
  </div>
</div>
';}).'';
}; ?>