<!DOCTYPE HTML>
<html>
  <head>
    <meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge" >
	<title></title>
	
	<!-- CSS -->
	<style>
	   body {
        width: 100%;
        height: 100%;
        margin: 0;
        padding: 0;
        background-color: #FAFAFA;
        font: 12pt "Tahoma";
    }
    * {
        box-sizing: border-box;
        -moz-box-sizing: border-box;
    }
    .page {
        width: 210mm;
        min-height: 297mm;
        padding: 20mm;
        margin: 10mm auto;
        border: 1px #D3D3D3 solid;
        border-radius: 5px;
        background: white;
        box-shadow: 0 0 5px rgba(0, 0, 0, 0.1);
    }
	
	h1{text-align:center;}
	h2{
	  font-size:1em;
	  font-weight:bold;
	  margin:5px 0;
	}
	
	.meta_table {
	  border-collapse: collapse;
	  margin-bottom:10px;
	}
	.meta_table td{
	  padding:5px;
	  border:1px #CDCDCD solid;
	}
	.meta_table td:nth-child(1){
	  width:20%;
	}
	
	.meta_field{
	  font-weight:bold;
	}
	.meta_value{
	  line-height:1.5em;
	}
	

	img , table{
	  width:100%;
	}
    .subpage {
        padding: 1cm;
        border: 5px red solid;
        height: 257mm;
        outline: 2cm #FFEAEA solid;
    }
	
    
	
	#image_page{
	  position:relative;
	}
	.reference{
	  position:absolute;
	  bottom:2cm;
	  font-weight:bold;
	  font-size:0.8em;
	}
	
	
    @page {
        size: A4;
        margin: 0;
    }
	
    @media print {
        html, body {
            width: 210mm;
            height: 297mm;        
        }
        .page {
            margin: 0;
            border: initial;
            border-radius: initial;
            width: initial;
            min-height: initial;
            box-shadow: initial;
            background: initial;
            page-break-after: always;
        }
    }
	  
	</style>  
	
  </head>
  <body>  
    <div class="book">
      <div class="page">
        
        <h1>國史館數位檔案檢索系</h1>
        
		<h2>列印資訊</h2>
		<table class="meta_table">
		    <tbody>
			    <tr class="meta_record"><td class="meta_field"> 輸出時間：</td><td class="meta_value">2016-09-07 15:20:26</td></tr> 
				<tr class="meta_record"><td class="meta_field"> 典藏號：</td><td class="meta_value"></td></tr> 
				<tr class="meta_record"><td class="meta_field"> 頁碼：</td><td class="meta_value">第 X 頁</td></tr> 
		    </tbody>
		</table>
		
		<h2>詮釋資料</h2>
		<table class="meta_table">
		    <tbody><tr class="meta_record"><td class="meta_field"> 典藏號：</td><td class="meta_value">001-010000-0006</td></tr> 
				<tr class="meta_record"><td class="meta_field"> 入藏登錄號：</td><td class="meta_value">001000000006A</td></tr> 
				<tr class="meta_record"><td class="meta_field"> 卷名：</td><td class="meta_value">中央機關還都南京籌備事項（五）</td></tr> 
				<tr class="meta_record"><td class="meta_field"> 檔案系列：</td><td class="meta_value"><search>國民政府檔案</search>/總類/總類總綱/總類總目</td></tr> 
				<tr class="meta_record">
				  <td colspan="2"> 
					<div class="meta_field">內容描述：</div>
					<div class="meta_value">國民政府委員會派員赴京辦理接收事宜，復員期間航運由戰運局籌統分配，中央黨政機關還都運輸實施辦法，各機關還都臨時費概算表</div> 
				  </td>
				</tr>   
				<tr class="meta_record"><td class="meta_field"> 日期起：</td><td class="meta_value">1945/09/10</td></tr> 
				<tr class="meta_record"><td class="meta_field"> 日期迄：</td><td class="meta_value">1945/11/01</td></tr> 
				<tr class="meta_record"><td class="meta_field"> 語文：</td><td class="meta_value">中文</td></tr> 
				<tr class="meta_record"><td class="meta_field"> 相關年代：</td><td class="meta_value">1945民國34年</td></tr> 
				<tr class="meta_record"><td class="meta_field"> 相關人員：</td><td class="meta_value"><persona>何應欽</persona>，<person>沈礪</person>，<person>馬超俊</person></td></tr> 
				<tr class="meta_record"><td class="meta_field"> 相關地點：</td><td class="meta_value"><location>重慶</location>，<location>南京</location></td></tr> 
		    </tbody>
		</table>

		
      </div>
      <div class="page" id='image_page'>
        <img src='image.jpg' /> 
		<div class='reference'><label>引用參照：</label>〈復員計畫綱要〉，《國民政府檔案》，國史館藏，典藏號：001-010000-0001，入藏登錄號：001000000001A。</div>
      </div>
    </div>

  </body>
</html>