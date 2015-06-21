/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
jQuery(document).ready(function(){
   jQuery('#categories_block_left li a').click(function(e){
        e.preventDefault();
        var ulChild = jQuery(this).parent().find('ul');
        var url = jQuery(this).attr('href')
        if(ulChild .length){
            jQuery(this).next().toggle();
        }else{
            window.location.href = url;
        }
    }) 
})


