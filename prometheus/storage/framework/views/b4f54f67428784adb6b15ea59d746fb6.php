<?php $__env->startSection('title', 'SPTheme'); ?>
<?php $__env->startSection('content'); ?>
<div class="container-fluid">
   <form method="POST" action="<?php echo e(route('admin.sptheme.storeSettings')); ?>">
      <?php echo csrf_field(); ?>
      <div class="row">
         <div class="col-12">
            <div class="card border-blue-bottom">
               <div class="content">
                  <div class="row">
                     <div class="col-lg-12">
                        <span style="float:right">
                           <button type="submit" formaction="<?php echo e(route('admin.sptheme.resetSettings')); ?>" onclick="return confirm('This will reset ALL SETTINGS to default. Are you sure?');" class="btn btn-danger">Reset to Default</button>
                           <button type="submit" class="btn btn-success">Save</button>
                        </span>
                        <h5>Base Settings</h5>
                        <div class="content table-responsive table-full-width">
                           <div class="row">
                              <table class="table table-hover table-responsive" id="spbasesettings">
                                 <tbody>
                                    <tr>
                                       <td>
                                          <p>Main logo URL</p>
                                          <p style="float:left; margin-right: 10px; margin-left: 2px;"><i class="fas fa-info-circle text-primary"></i> Your virtual airline logo on the top right corner.</p>
                                       </td>
                                       <td>
                                          <input type="text" id="sp_logo_url" name="sp_logo_url" class="form-control" value="<?php echo e($sp_settings['logo_url'] ?? ''); ?>" placeholder="https://www.domain.tld/logo.png">
                                       </td>
                                    </tr>
                                    <tr>
                                       <td>
                                          <p>Airline slogan</p>
                                          <p style="float:left; margin-right: 10px; margin-left: 2px;"><i class="fas fa-info-circle text-primary"></i> This slogan will be shown below your airline name. (empty = disabled)</p>
                                       </td>
                                       <td>
                                          <input type="text" id="sp_slogan" name="sp_slogan" class="form-control" value="<?php echo e($sp_settings['slogan'] ?? ''); ?>" placeholder="We fly all over the world">
                                       </td>
                                    </tr>
                                    <tr>
                                       <td>
                                          <p>Youtube URL</p>
                                          <p style="float:left; margin-right: 10px; margin-left: 2px;"><i class="fas fa-info-circle text-primary"></i> The URL to your virtual airline youtube channel. (empty = disabled)</p>
                                       </td>
                                       <td>
                                          <input type="text" id="sp_youtube" name="sp_youtube" class="form-control" value="<?php echo e($sp_settings['youtube'] ?? ''); ?>" placeholder="https://www.youtube.com/...">
                                       </td>
                                    </tr>
                                    <tr>
                                       <td>
                                          <p>´Discord URL</p>
                                          <p style="float:left; margin-right: 10px; margin-left: 2px;"><i class="fas fa-info-circle text-primary"></i> The URL to your virtual airline discord channel. (empty = disabled)</p>
                                       </td>
                                       <td>
                                          <input type="text" id="sp_discord" name="sp_discord" class="form-control" value="<?php echo e($sp_settings['discord'] ?? ''); ?>" placeholder="https://www.discord.com/...">
                                       </td>
                                    </tr>
                                    <tr>
                                       <td>
                                          <p>Instagram URL</p>
                                          <p style="float:left; margin-right: 10px; margin-left: 2px;"><i class="fas fa-info-circle text-primary"></i> The URL to your virtual airline instagram channel. (empty = disabled)</p>
                                       </td>
                                       <td>
                                          <input type="text" id="sp_instagram" name="sp_instagram" class="form-control" value="<?php echo e($sp_settings['instagram'] ?? ''); ?>" placeholder="https://www.instagram.com/...">
                                       </td>
                                    </tr>
                                    <tr>
                                       <td>
                                          <p>Staff Role</p>
                                          <p style="float:left; margin-right: 10px; margin-left: 2px;"><i class="fas fa-info-circle text-primary"></i> Enter the name of your role that is a member of your staff team.</p>
                                       </td>
                                       <td>
                                          <input type="text" id="sp_staff" name="sp_staff" class="form-control" value="<?php echo e($sp_settings['staff'] ?? ''); ?>" placeholder="staff">
                                       </td>
                                    </tr>
                                    <tr>
                                       <td>
                                          <p>User Field Name (IVAO)</p>
                                          <p style="float:left; margin-right: 10px; margin-left: 2px;"><i class="fas fa-info-circle text-primary"></i> Enter the name of your custom user field where the user can enter his ivao id.</p>
                                       </td>
                                       <td>
                                          <input type="text" id="sp_fieldivao" name="sp_fieldivao" class="form-control" value="<?php echo e($sp_settings['fieldivao'] ?? ''); ?>" placeholder="IVAO ID">
                                       </td>
                                    </tr>
                                    <tr>
                                       <td>
                                          <p>User Field Name (VATSIM)</p>
                                          <p style="float:left; margin-right: 10px; margin-left: 2px;"><i class="fas fa-info-circle text-primary"></i> Enter the name of your custom user field where the user can enter his vatsim id.</p>
                                       </td>
                                       <td>
                                          <input type="text" id="sp_fieldvatsim" name="sp_fieldvatsim" class="form-control" value="<?php echo e($sp_settings['fieldvatsim'] ?? ''); ?>" placeholder="VATSIM ID">
                                       </td>
                                    </tr>
                                    <tr>
                                       <td>
                                          <p>User Field Name (DISCORD)</p>
                                          <p style="float:left; margin-right: 10px; margin-left: 2px;"><i class="fas fa-info-circle text-primary"></i> Enter the name of your custom user field where the user can enter his discord id.</p>
                                       </td>
                                       <td>
                                          <input type="text" id="sp_fielddiscord" name="sp_fielddiscord" class="form-control" value="<?php echo e($sp_settings['fielddiscord'] ?? ''); ?>" placeholder="DISCORD ID">
                                       </td>
                                    </tr>
                                    <tr>
                                       <td>
                                          <p>Show Rules</p>
                                          <p style="float:left; margin-right: 10px; margin-left: 2px;"><i class="fas fa-info-circle text-primary"></i> This will show a box with some rules during the registration.</p>
                                       </td>
                                       <td align="center">
                                          <input type="hidden" name="sp_registerrules_on" value="0">
                                          <input type="checkbox" id="sp_registerrules_on" name="sp_registerrules_on" value="1" class="form-control" <?php echo e($sp_settings['registerrules_on'] ? 'checked' : ''); ?>>
                                       </td>
                                    </tr>
                                    <tr>
                                       <td>
                                          <p>Rules Text</p>
                                          <p style="float:left; margin-right: 10px; margin-left: 2px;"><i class="fas fa-info-circle text-primary"></i> This is the content of your rules box on the register page.</p>
                                       </td>
                                       <td>
                                          <textarea id="sp_registerrules_text" name="sp_registerrules_text" class="form-control" rows="10"><?php echo e($sp_settings['registerrules_text'] ?? ''); ?></textarea>
                                       </td>
                                    </tr>
                                 </tbody>
                              </table>
                           </div>
                        </div>
                     </div>
                  </div>
               </div>
            </div>
         </div>
      </div>
      <div class="row">
         <div class="col-12">
            <div class="card border-blue-bottom">
               <div class="content">
                  <div class="row">
                     <div class="col-lg-12">
                        <span style="float:right">
                           <button type="submit" formaction="<?php echo e(route('admin.sptheme.resetSettings')); ?>" onclick="return confirm('This will reset ALL SETTINGS to default. Are you sure?');" class="btn btn-danger">Reset to Default</button>
                           <button type="submit" class="btn btn-success">Save</button>
                        </span>
                        <h5>Color Settings</h5>
                        <div class="content table-responsive table-full-width">
                           <div class="row">
                              <table class="table table-hover table-responsive" id="spbasesettings">
                                 <tbody>
                                    <tr>
                                       <td>
                                          <p>Text Main Color</p>
                                          <p style="float:left; margin-right: 10px; margin-left: 2px;"><i class="fas fa-info-circle text-primary"></i> This is the main content text color.</p>
                                       </td>
                                       <td class="text-right">
                                          <input type="color" id="sp_textmaincolor" name="sp_textmaincolor" class="form-control" value="<?php echo e($sp_settings['textmaincolor']); ?>">
                                       </td>
                                    </tr>
                                    <tr>
                                       <td>
                                          <p>Text Accent Color</p>
                                          <p style="float:left; margin-right: 10px; margin-left: 2px;"><i class="fas fa-info-circle text-primary"></i> Bottoms, icons, button and text color.</p>
                                       </td>
                                       <td class="text-right">
                                          <input type="color" id="sp_textaccentcolor" name="sp_textaccentcolor" class="form-control" value="<?php echo e($sp_settings['textaccentcolor']); ?>">
                                       </td>
                                    </tr>
                                    <tr>
                                       <td>
                                          <p>Text Sidebar Color</p>
                                          <p style="float:left; margin-right: 10px; margin-left: 2px;"><i class="fas fa-info-circle text-primary"></i> Sidebar and top navigation text/icons.</p>
                                       </td>
                                       <td class="text-right">
                                          <input type="color" id="sp_textsidebarcolor" name="sp_textsidebarcolor" class="form-control" value="<?php echo e($sp_settings['textsidebarcolor']); ?>">
                                       </td>
                                    </tr>
                                    <tr>
                                       <td>
                                          <p>Text Sub Color</p>
                                          <p style="float:left; margin-right: 10px; margin-left: 2px;"><i class="fas fa-info-circle text-primary"></i> For some light subtexts below main content.</p>
                                       </td>
                                       <td class="text-right">
                                          <input type="color" id="sp_textsubcolor" name="sp_textsubcolor" class="form-control" value="<?php echo e($sp_settings['textsubcolor']); ?>">
                                       </td>
                                    </tr>
                                    <tr>
                                       <td>
                                          <p>Background Main Color</p>
                                          <p style="float:left; margin-right: 10px; margin-left: 2px;"><i class="fas fa-info-circle text-primary"></i> This is the main background color.</p>
                                       </td>
                                       <td class="text-right">
                                          <input type="color" id="sp_bgmaincolor" name="sp_bgmaincolor" class="form-control" value="<?php echo e($sp_settings['bgmaincolor']); ?>">
                                       </td>
                                    </tr>
                                    <tr>
                                       <td>
                                          <p>Background Bar Color</p>
                                          <p style="float:left; margin-right: 10px; margin-left: 2px;"><i class="fas fa-info-circle text-primary"></i> Background for the sidebar, navigation and footer.</p>
                                       </td>
                                       <td class="text-right">
                                          <input type="color" id="sp_bgbarcolor" name="sp_bgbarcolor" class="form-control" value="<?php echo e($sp_settings['bgbarcolor']); ?>">
                                       </td>
                                    </tr>
                                    <tr>
                                       <td>
                                          <p>Background Accent Color</p>
                                          <p style="float:left; margin-right: 10px; margin-left: 2px;"><i class="fas fa-info-circle text-primary"></i> Bottoms, icons, button and accent color.</p>
                                       </td>
                                       <td class="text-right">
                                          <input type="color" id="sp_bgaccentcolor" name="sp_bgaccentcolor" class="form-control" value="<?php echo e($sp_settings['bgaccentcolor']); ?>">
                                       </td>
                                    </tr>
                                    <tr>
                                       <td>
                                          <p>Background Accent Color 2</p>
                                          <p style="float:left; margin-right: 10px; margin-left: 2px;"><i class="fas fa-info-circle text-primary"></i> This will be used for style elements and background accent.</p>
                                       </td>
                                       <td class="text-right">
                                          <input type="color" id="sp_bgaccentcolor2" name="sp_bgaccentcolor2" class="form-control" value="<?php echo e($sp_settings['bgaccentcolor2']); ?>">
                                       </td>
                                    </tr>
                                    <tr>
                                       <td>
                                          <p>Hover Accent Color</p>
                                          <p style="float:left; margin-right: 10px; margin-left: 2px;"><i class="fas fa-info-circle text-primary"></i> Underlines, icons, button and accent color.</p>
                                       </td>
                                       <td class="text-right">
                                          <input type="color" id="sp_hoveraccentcolor" name="sp_hoveraccentcolor" class="form-control" value="<?php echo e($sp_settings['hoveraccentcolor']); ?>">
                                       </td>
                                    </tr>
                                    <tr>
                                       <td>
                                          <p>Link Color</p>
                                          <p style="float:left; margin-right: 10px; margin-left: 2px;"><i class="fas fa-info-circle text-primary"></i> Most of the content links will get this color.</p>
                                       </td>
                                       <td class="text-right">
                                          <input type="color" id="sp_linkcolor" name="sp_linkcolor" class="form-control" value="<?php echo e($sp_settings['linkcolor']); ?>">
                                       </td>
                                    </tr>
                                    <tr>
                                       <td>
                                          <p>Link Color 2</p>
                                          <p style="float:left; margin-right: 10px; margin-left: 2px;"><i class="fas fa-info-circle text-primary"></i> Some of the module links will get this color.</p>
                                       </td>
                                       <td class="text-right">
                                          <input type="color" id="sp_linkcolor2" name="sp_linkcolor2" class="form-control" value="<?php echo e($sp_settings['linkcolor2']); ?>">
                                       </td>
                                    </tr>
                                    <tr>
                                       <td>
                                          <p>Link Hover Color</p>
                                          <p style="float:left; margin-right: 10px; margin-left: 2px;"><i class="fas fa-info-circle text-primary"></i> Most of the content links on mouse hover.</p>
                                       </td>
                                       <td class="text-right">
                                          <input type="color" id="sp_linkhovercolor" name="sp_linkhovercolor" class="form-control" value="<?php echo e($sp_settings['linkhovercolor']); ?>">
                                       </td>
                                    </tr>
                                    <tr>
                                       <td>
                                          <p>Card Border Color</p>
                                          <p style="float:left; margin-right: 10px; margin-left: 2px;"><i class="fas fa-info-circle text-primary"></i> All card borders around the content.</p>
                                       </td>
                                       <td class="text-right">
                                          <input type="color" id="sp_cardbordercolor" name="sp_cardbordercolor" class="form-control" value="<?php echo e($sp_settings['cardbordercolor']); ?>">
                                       </td>
                                    </tr>
                                    <tr>
                                       <td>
                                          <p>Background Hover Color</p>
                                          <p style="float:left; margin-right: 10px; margin-left: 2px;"><i class="fas fa-info-circle text-primary"></i> Most for any content mouse over effect like in tables.</p>
                                       </td>
                                       <td class="text-right">
                                          <input type="color" id="sp_bghovercolor" name="sp_bghovercolor" class="form-control" value="<?php echo e($sp_settings['bghovercolor']); ?>">
                                       </td>
                                    </tr>
                                    <tr>
                                       <td>
                                          <p>Background Input Color</p>
                                          <p style="float:left; margin-right: 10px; margin-left: 2px;"><i class="fas fa-info-circle text-primary"></i> This will appear to all input fields.</p>
                                       </td>
                                       <td class="text-right">
                                          <input type="color" id="sp_bginputcolor" name="sp_bginputcolor" class="form-control" value="<?php echo e($sp_settings['bginputcolor']); ?>">
                                       </td>
                                    </tr>
                                    <tr>
                                       <td>
                                          <p>Background Card Color</p>
                                          <p style="float:left; margin-right: 10px; margin-left: 2px;"><i class="fas fa-info-circle text-primary"></i> This is the main background color for all cards.</p>
                                       </td>
                                       <td class="text-right">
                                          <input type="color" id="sp_bgcardcolor" name="sp_bgcardcolor" class="form-control" value="<?php echo e($sp_settings['bgcardcolor']); ?>">
                                       </td>
                                    </tr>
                                    <tr>
                                       <td>
                                          <p>Border Accent Color</p>
                                          <p style="float:left; margin-right: 10px; margin-left: 2px;"><i class="fas fa-info-circle text-primary"></i> Additional borders around some accent lines.</p>
                                       </td>
                                       <td class="text-right">
                                          <input type="color" id="sp_borderaccentcolor" name="sp_borderaccentcolor" class="form-control" value="<?php echo e($sp_settings['borderaccentcolor']); ?>">
                                       </td>
                                    </tr>
                                    <tr>
                                       <td>
                                          <p>Sidebar Border Color</p>
                                          <p style="float:left; margin-right: 10px; margin-left: 2px;"><i class="fas fa-info-circle text-primary"></i> Dividing line between the sidebar and the main content.</p>
                                       </td>
                                       <td class="text-right">
                                          <input type="color" id="sp_sidebarbordercolor" name="sp_sidebarbordercolor" class="form-control" value="<?php echo e($sp_settings['sidebarbordercolor']); ?>">
                                       </td>
                                    </tr>
                                    <tr>
                                       <td>
                                          <p>Badge Text Color</p>
                                          <p style="float:left; margin-right: 10px; margin-left: 2px;"><i class="fas fa-info-circle text-primary"></i> Similar to the Buttons but for all badges.</p>
                                       </td>
                                       <td class="text-right">
                                          <input type="color" id="sp_badgetextcolor" name="sp_badgetextcolor" class="form-control" value="<?php echo e($sp_settings['badgetextcolor']); ?>">
                                       </td>
                                    </tr>
                                 </tbody>
                              </table>
                           </div>
                        </div>
                     </div>
                  </div>
               </div>
            </div>
         </div>
      </div>
      <div class="row">
         <div class="col-12">
            <div class="card border-blue-bottom">
               <div class="content">
                  <div class="row">
                     <div class="col-lg-12">
                        <span style="float:right">
                           <button type="submit" formaction="<?php echo e(route('admin.sptheme.resetSettings')); ?>" onclick="return confirm('This will reset ALL SETTINGS to default. Are you sure?');" class="btn btn-danger">Reset to Default</button>
                           <button type="submit" class="btn btn-success">Save</button>
                        </span>
                        <h5>Home Settings</h5>
                        <div class="content table-responsive table-full-width">
                           <div class="row">
                              <table class="table table-hover table-responsive" id="sphomesettings">
                                 <tbody>
                                    <tr>
                                       <td>
                                          <p>Show world clock</p>
                                          <p style="float:left; margin-right: 10px; margin-left: 2px;"><i class="fas fa-info-circle text-primary"></i> If you want the world clock enabled.</p>
                                       </td>
                                       <td align="center">
                                          <input type="hidden" name="sp_worldclock_on" value="0">
                                          <input type="checkbox" id="sp_worldclock_on" name="sp_worldclock_on" value="1" class="form-control" <?php echo e($sp_settings['worldclock_on'] ? 'checked' : ''); ?>>
                                       </td>
                                    </tr>
                                    <tr>
                                       <td>
                                          <p>Welcome Text</p>
                                          <p style="float:left; margin-right: 10px; margin-left: 2px;"><i class="fas fa-info-circle text-primary"></i> This is the main welcome content home page.</p>
                                       </td>
                                       <td>
                                          <textarea id="sp_welcome_text" name="sp_welcome_text" class="form-control" rows="10"><?php echo e($sp_settings['welcome_text'] ?? ''); ?></textarea>
                                       </td>
                                    </tr>
                                    <tr>
                                       <td>
                                          <p>Left box title</p>
                                          <p style="float:left; margin-right: 10px; margin-left: 2px;"><i class="fas fa-info-circle text-primary"></i> This is the title of your left box.</p>
                                       </td>
                                       <td>
                                          <input type="text" id="sp_leftbox_title" name="sp_leftbox_title" class="form-control" value="<?php echo e($sp_settings['leftbox_title'] ?? ''); ?>" placeholder="Title Box Left">
                                       </td>
                                    </tr>
                                    <tr>
                                       <td>
                                          <p>Left box Text</p>
                                          <p style="float:left; margin-right: 10px; margin-left: 2px;"><i class="fas fa-info-circle text-primary"></i> This is the content of your left box.</p>
                                       </td>
                                       <td>
                                          <textarea id="sp_leftbox_text" name="sp_leftbox_text" class="form-control" rows="10"><?php echo e($sp_settings['leftbox_text'] ?? ''); ?></textarea>
                                       </td>
                                    </tr>
                                    <tr>
                                       <td>
                                          <p>Right box title</p>
                                          <p style="float:left; margin-right: 10px; margin-left: 2px;"><i class="fas fa-info-circle text-primary"></i> This is the title of your right box.</p>
                                       </td>
                                       <td>
                                          <input type="text" id="sp_rightbox_title" name="sp_rightbox_title" class="form-control" value="<?php echo e($sp_settings['rightbox_title'] ?? ''); ?>" placeholder="Title Box Right">
                                       </td>
                                    </tr>
                                    <tr>
                                       <td>
                                          <p>Right box Text</p>
                                          <p style="float:left; margin-right: 10px; margin-left: 2px;"><i class="fas fa-info-circle text-primary"></i> This is the content of your right box.</p>
                                       </td>
                                       <td>
                                          <textarea id="sp_rightbox_text" name="sp_rightbox_text" class="form-control" rows="10"><?php echo e($sp_settings['rightbox_text'] ?? ''); ?></textarea>
                                       </td>
                                    </tr>
                                    <tr>
                                       <td>
                                          <p>Show bottom box</p>
                                          <p style="float:left; margin-right: 10px; margin-left: 2px;"><i class="fas fa-info-circle text-primary"></i> If you want the bottom box nabled.</p>
                                       </td>
                                       <td align="center">
                                          <input type="hidden" name="sp_bottombox_on" value="0">
                                          <input type="checkbox" id="sp_bottombox_on" name="sp_bottombox_on" value="1" class="form-control" <?php echo e($sp_settings['bottombox_on'] ? 'checked' : ''); ?>>
                                       </td>
                                    </tr>
                                    <tr>
                                       <td>
                                          <p>Bottom box title</p>
                                          <p style="float:left; margin-right: 10px; margin-left: 2px;"><i class="fas fa-info-circle text-primary"></i> This is the title of your bottom box.</p>
                                       </td>
                                       <td>
                                          <input type="text" id="sp_bottombox_title" name="sp_bottombox_title" class="form-control" value="<?php echo e($sp_settings['bottombox_title'] ?? ''); ?>" placeholder="Title Box Bottom">
                                       </td>
                                    </tr>
                                    <tr>
                                       <td>
                                          <p>Bottom box Text</p>
                                          <p style="float:left; margin-right: 10px; margin-left: 2px;"><i class="fas fa-info-circle text-primary"></i> This is the content of your bottom box.</p>
                                       </td>
                                       <td>
                                          <textarea id="sp_bottombox_text" name="sp_bottombox_text" class="form-control" rows="10"><?php echo e($sp_settings['bottombox_text'] ?? ''); ?></textarea>
                                       </td>
                                    </tr>
                                    <tr>
                                       <td>
                                          <p>Show carousel</p>
                                          <p style="float:left; margin-right: 10px; margin-left: 2px;"><i class="fas fa-info-circle text-primary"></i> If you want the logo carousel enabled.</p>
                                       </td>
                                       <td align="center">
                                          <input type="hidden" name="sp_carousel_on" value="0">
                                          <input type="checkbox" id="sp_carousel_on" name="sp_carousel_on" value="1" class="form-control" <?php echo e($sp_settings['carousel_on'] ? 'checked' : ''); ?>>
                                       </td>
                                    </tr>
                                    <tr>
                                       <td>
                                          <p>Logo carousel URL 5</p>
                                          <p style="float:left; margin-right: 10px; margin-left: 2px;"><i class="fas fa-info-circle text-primary"></i> Enter the URL where the carousel image should lead to. (empty = not linked)</p>
                                       </td>
                                       <td>
                                          <input type="text" id="sp_carousel_url_5" name="sp_carousel_url_5" class="form-control" value="<?php echo e($sp_settings['carousel_url_5'] ?? ''); ?>" placeholder="https://www.domain.tld">
                                       </td>
                                    </tr>
                                    <tr>
                                       <td>
                                          <p>Logo carousel URL 6</p>
                                          <p style="float:left; margin-right: 10px; margin-left: 2px;"><i class="fas fa-info-circle text-primary"></i> Enter the URL where the carousel image should lead to. (empty = not linked)</p>
                                       </td>
                                       <td>
                                          <input type="text" id="sp_carousel_url_6" name="sp_carousel_url_6" class="form-control" value="<?php echo e($sp_settings['carousel_url_6'] ?? ''); ?>" placeholder="https://www.domain.tld">
                                       </td>
                                    </tr>
                                    <tr>
                                       <td>
                                          <p>Logo carousel URL 7</p>
                                          <p style="float:left; margin-right: 10px; margin-left: 2px;"><i class="fas fa-info-circle text-primary"></i> Enter the URL where the carousel image should lead to. (empty = not linked)</p>
                                       </td>
                                       <td>
                                          <input type="text" id="sp_carousel_url_7" name="sp_carousel_url_7" class="form-control" value="<?php echo e($sp_settings['carousel_url_7'] ?? ''); ?>" placeholder="https://www.domain.tld">
                                       </td>
                                    </tr>
                                    <tr>
                                       <td>
                                          <p>Logo carousel URL 8</p>
                                          <p style="float:left; margin-right: 10px; margin-left: 2px;"><i class="fas fa-info-circle text-primary"></i> Enter the URL where the carousel image should lead to. (empty = not linked)</p>
                                       </td>
                                       <td>
                                          <input type="text" id="sp_carousel_url_8" name="sp_carousel_url_8" class="form-control" value="<?php echo e($sp_settings['carousel_url_8'] ?? ''); ?>" placeholder="https://www.domain.tld">
                                       </td>
                                    </tr>
                                    <tr>
                                       <td>
                                          <p>Logo carousel URL 9</p>
                                          <p style="float:left; margin-right: 10px; margin-left: 2px;"><i class="fas fa-info-circle text-primary"></i> Enter the URL where the carousel image should lead to. (empty = not linked)</p>
                                       </td>
                                       <td>
                                          <input type="text" id="sp_carousel_url_9" name="sp_carousel_url_9" class="form-control" value="<?php echo e($sp_settings['carousel_url_9'] ?? ''); ?>" placeholder="https://www.domain.tld">
                                       </td>
                                    </tr>
                                 </tbody>
                              </table>
                           </div>
                        </div>
                     </div>
                  </div>
               </div>
            </div>
         </div>
      </div>
   </form>
   <form method="POST" action="<?php echo e(route('admin.sptheme.storeCrewtest')); ?>">
      <?php echo csrf_field(); ?>
      <div class="row">
         <div class="col-12">
            <div class="card border-blue-bottom">
               <div class="content">
                  <div class="row">
                     <div class="col-lg-12">
                        <span style="float:right">
                           <button type="submit" formaction="<?php echo e(route('admin.sptheme.resetSettings')); ?>" onclick="return confirm('This will reset ALL SETTINGS to default. Are you sure?');" class="btn btn-danger">Reset to Default</button>
                           <button type="submit" class="btn btn-success">Save</button>
                        </span>
                        <h5>Crew Test Settings</h5>
                        <div class="content table-responsive table-full-width">
                           <div class="row">
                              <table class="table table-hover table-responsive" id="sphomesettings">
                                 <tbody>
                                    <tr>
                                       <td>
                                          <p>Show Test</p>
                                          <p style="float:left; margin-right: 10px; margin-left: 2px;"><i class="fas fa-info-circle text-primary"></i> This enables a short test before a user can register.</p>
                                       </td>
                                       <td align="center">
                                          <input type="hidden" name="sp_registertest_on" value="0">
                                          <input type="checkbox" id="sp_registertest_on" name="sp_registertest_on" value="1" class="form-control" <?php echo e($sp_crewtest[0]['registertest_on'] ? 'checked' : ''); ?>>
                                       </td>
                                    </tr>
                                    <tr>
                                       <td>
                                          <p>Questions and Answers</p>
                                          <p style="float:left; margin-right: 10px; margin-left: 2px;"><i class="fas fa-info-circle text-primary"></i> If you want to change the questions and answers please edit the specific file:</p>
                                       </td>
                                       <td align="center">
                                          <p>/public/SPTheme/js/quiz.json</p>
                                       </td>
                                    </tr>
                                 </tbody>
                              </table>
                           </div>
                        </div>
                     </div>
                  </div>
               </div>
            </div>
         </div>
      </div>
   </form>
   <p class="text-center">Crafted with <i class="fas fa-heart text-danger"></i> by <a href="https://github.com/PaintSplasher/phpvms7_sptransfer" target="_blank">Sass-Projects</p>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('sptheme::layouts.admin', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/jewe0363/prometheus/modules/SPTheme/Providers/../Resources/views/admin/index.blade.php ENDPATH**/ ?>