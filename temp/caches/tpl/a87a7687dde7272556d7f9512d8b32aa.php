<?php exit;?>001476941281edda7062e7ee84ee4f8ba1e7332d8dcas:1452:"a:2:{s:8:"template";s:1388:"<?php $__Template->display($this->getTpl("page_header")); ?>
	<body class="b-color-f">
		<div id="loading"><img src="<?php echo __TPL__;?>img/loading.gif" /></div>
		<div class="con">
			<!--根据邮箱找回-->
			<section class="user-center j-f-email margin-lr">
				<form action="<?php echo U('user/profile/user_edit_email');?>" method="post">
					<div class="text-all dis-box j-text-all">
						<div class="input-text box-flex" name="emaildiv">
							<input class="j-input-text" id="focus" type="text" name="email" placeholder="<?php if($emails) { ?><?php echo $emails; ?><?php } else { ?>请输入邮箱<?php } ?>" />
							<i class="iconfont icon-guanbi1 is-null j-is-null"></i>
						</div>
					</div>
					<button type="submit" class="btn-submit">确定修改</button>
				</form>
			</section>
		</div>
	</body>
	
	<script>
	$("#focus").focus();
	$(".btn-submit").click(function(){
		 var email = $("#focus");
		 var reg = /^([a-zA-Z0-9]+[_|\_|\.]?)*[a-zA-Z0-9]+@([a-zA-Z0-9]+[_|\_|\.]?)*[a-zA-Z0-9]+\.[a-zA-Z]{2,3}$/;
		if(email.val() == ""){
			d_messages('请输入用户邮箱',2);
			 $("div[name=emaildiv]").addClass("active");
			return false;
		}
		if(!reg.test(email.val())){
			 d_messages('请输入有效的邮箱',2);
			 $("div[name=emaildiv]").addClass("active");
			 return false;
	     }
			
		
	})
	</script>

</html>";s:12:"compile_time";i:1476854881;}";