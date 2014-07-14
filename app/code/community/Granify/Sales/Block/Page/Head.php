<?php
/**
 * Page head block for render HTML tag with granify JS script
 *
 * @category    Granify
 * @package     Granify_Sales
 */
class Granify_Sales_Block_Page_Head extends Mage_Core_Block_Template
{
    /**
     * Get default granify js
     *
     * @return string
     */
    public function getDefaultGranifyJsLink()
    {
        /** @var $helper Granify_Sales_Helper_Data */
        $helper = $this->helper('granify_sales');
        if (!$helper->isAble()) {
            return '';
        }
        return $helper->getDefaultGranifyJsLink();
    }

    /**
     * Get JavaScript with injected link
     *
     * @return string
     */
    public function getJsScript()
    {
        $link = $this->getDefaultGranifyJsLink();
        if (!$link) {
            return '';
        }
        
        list($link_url, $site_id) = explode("?", $link);
        $site_id = str_replace("id=", "", $site_id);

        return sprintf(
            '<script type="text/javascript">
            var GRANIFY_SITE_ID = %d;
 
            (function(e,t,n){e=e+"?id="+t;window.Granify=n;n._stack=[];n.s_v=2;n.init=function(e,t,r){function i(e,t){e[t]=function(){Granify._stack.push([t].concat(Array.prototype.slice.call(arguments,0)))}}var s=n;h=["on","addTag","trackPageView","trackCart","trackOrder"];for(u=0;u<h.length;u++)i(s,h[u])};n.init();var r,i,s,o=document.createElement("iframe");o.src="javascript:false";o.title="";o.role="presentation";(o.frameElement||o).style.cssText="width: 0; height: 0; border: 0";s=document.getElementsByTagName("script");s=s[s.length-1];s.parentNode.insertBefore(o,s);try{i=o.contentWindow.document}catch(u){r=document.domain;o.src="javascript:var d=document.open();d.domain=\'"+r+"\';void(0);";i=o.contentWindow.document}i.open()._l=function(){var t=this.createElement("script");if(r)this.domain=r;t.id="js-iframe-async";t.src=e;this.body.appendChild(t)};i.write(\'<body onload="document._l();">\');i.close()})("%s",GRANIFY_SITE_ID,[])
            </script>',
            $site_id,
            $link_url
        ) . PHP_EOL;
    }

    /**
     * Render HTML
     *
     * @return string
     */
    protected function _toHtml()
    {
        return $this->getJsScript();
    }
}
