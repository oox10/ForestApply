    <div id='contect_master'>  
	  <div class='container footer-block'>
	    
		<div class='information'>
		  <h2> 相關文件 </h2>
		  <ul>
		    <li> <a href='docs/林務局自然保護區域進入申請系統申請流程圖2018.pdf' target=_blank >申請流程</a>  </li>
		    <li> 申請規則說明</a></li>
		    <li> 申請須知 </li>
		    <li>          </li>
		  </ul>
		  <?php if(!$this->vars['server']['data']['area']['alone']): ?> 
		  <h2> 相關連結 </h2>
		  <ul>
		    <li> <a href='http://www.forest.gov.tw' target=_blank >林務局全球資訊網</a>  </li>
		    <li> <a href='http://conservation.forest.gov.tw/' target=_blank >林務局自然保育網</a> </li>
			
		    
		  </ul>
		  <?php endif; ?>
		  
		  <h2> 語言設定 </h2>
		  <div id="google_translate_element"></div>
		  <script type="text/javascript">
				function googleTranslateElementInit() {
					new google.translate.TranslateElement({pageLanguage: 'zh-TW', includedLanguages: 'en,ja,zh-TW', layout: google.translate.TranslateElement.InlineLayout.SIMPLE}, 'google_translate_element');
				}
		  </script>
		  <script type="text/javascript" src="//translate.google.com/translate_a/element.js?cb=googleTranslateElementInit"></script> 
		
		
		</div>
	    
	    <div class='contact'>
		  <h2>聯絡資訊</h2>
		  <table class='area_admin'>
			<tr>
			  <td width=200> <i class="fa fa-university" aria-hidden="true"></i> 各區主管單位 </td>
			  <td width=200> <i class="fa fa-phone" aria-hidden="true"></i> 聯絡方式 </td>
			  <td> <i class="fa fa-map-marker" aria-hidden="true"></i> 所轄保護/保留區</td>
			</tr>
			<tbody>
			<?php foreach($area_contact as $gcode=>$ginfo): ?>    
			<?php  if(count($ginfo['areas']) && isset($ginfo['contact']) && count($ginfo['contact']) ): ?>   
				<tr>
					<td><?php echo $ginfo['organ']; ?> - <?php echo $ginfo['contact'][0]['user_organ']; ?></td>
					<td><?php echo $ginfo['contact'][0]['user_tel']; ?></td>
					<td >
					   <?php echo join('、',$ginfo['areas']); ?>
					</td>
			    </tr>
			<?php   endif; ?>    	
			<?php endforeach; ?>
			</tbody>
			<!--
			<tr>
			  <td> <i class="fa fa-university" aria-hidden="true"></i> 主管機關 </td>
			</tr>
			<tr>
			  <td>行政院農業委員會林務局</td>
			  <td colspan=2> 
			      單位地址：10050 台北市杭州南路1段2號、電話：02-23515441分機673 <br/>服務信箱：paadmin@forest.gov.tw
			  </td>
			</tr>
			-->
          </table>
		</div>
	  </div>	
	  <div class='container bottom-block'>
		  <div class='copyright'>
			<div>
			  Copyright © 2017 Forestry Bureau  行政院農業委員會林務局版權所有
			</div>
			<div class='site_mark'>
			  <img src='theme/image/mark_forest_area.png' style='width:35px;'/>
			  <img src='theme/image/mark_forest.png' />
			  <img src='theme/image/mark_gov.jpg' />  
			</div>
		  </div>
	  
	     
	  </div>
	</div>	
		
	 