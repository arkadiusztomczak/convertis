{assign var='prCategories' value=Product::getProductCategories($product.id)}
{assign var='show' value=0}
{foreach from=$prCategories item=i}
   {if in_array($i, $categories)}
      {assign var='show' value=1}
   {/if}
{/foreach}
{if $show eq 1}
<div class="ep_overlay">
   <div class="ep_window" style="background:{$bgColor}; border-color: {$fontColor}">
      <div class="container">
         <div class="row">
            <div class="col-sm-2 ep_leftSidebar ep_sidebar" style="background-image: url('{$img}')"></div>
            <span class="col-sm-10 ep_rightSidebar ep_sidebar">
               <div class="ep_header" style="color: {$fontColor}">{$header}</div>
               <div class="ep_content" style="color: {$fontColor}">{$content nofilter}</div>

               <div class="ep_button" style="color:{$bgColor}; background: {$fontColor}">{$button} <span class="ep_button_arrow">→</span></div>
            </div>
         </div>
      </div>
   </div>
</div>
{/if}