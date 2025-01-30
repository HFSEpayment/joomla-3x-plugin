<?php
defined('_JEXEC') or die('Restricted access');
?>
<div class="col100">
<fieldset class="adminform">
<table class="admintable" width = "100%" >

<tr>
   <td style="width:250px;" class="key">
     <?php echo _JSHOP_HBEPAY_CLIENT_ID;?>
   </td>
   <td>
     <input type = "text" class = "inputbox" name = "pm_params[client_id]" size="45" value = "<?php echo $params['client_id']?>" />
   </td>
 </tr>
 
 <tr>
   <td style="width:250px;" class="key">
     <?php echo _JSHOP_HBEPAY_CLIENT_SECRET;?>
   </td>
   <td>
     <input type = "text" class = "inputbox" name = "pm_params[client_secret]" size="45" value = "<?php echo $params['client_secret']?>" />
   </td>
 </tr>

   <tr>
   <td style="width:250px;" class="key">
     <?php echo _JSHOP_HBEPAY_TERMINAL;?>
   </td>
   <td>
     <input type = "text" class = "inputbox" name = "pm_params[terminal]" size="45" value = "<?php echo $params['terminal']?>" />
   </td>
  `</tr>

 <tr>
   <td style="width:250px;" class="key">
     <?php echo _JSHOP_HBEPAY_MODE?>
   </td>
   <td>
     <?php              
     print JHTML::_('select.booleanlist', 'pm_params[testmode]', 'class = "inputbox"', $params['testmode']);
     ?>
   </td>
 </tr>
</table>
</fieldset>
</div>
<div class="clr"></div>