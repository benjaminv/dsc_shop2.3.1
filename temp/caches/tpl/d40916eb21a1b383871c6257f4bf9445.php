<?php exit;?>0014798035704a85e93006178d1eea52fa418a341ab6s:7105:"a:2:{s:8:"template";s:7041:"<?php $__Template->display($this->getTpl("page_header")); ?>
<body class="b-color-f">
		<!-- <?php if($sms == 1) { ?> -->
		<div class="con" id="pjax-container">
            <div id="show">
				<section class="user-center j-f-tel margin-lr">
					<div class="text-all dis-box j-text-all" name="write_mobilediv" >
						<label>+86</label>
						<div class="box-flex input-text">
							<input class="j-input-text" name="write_mobile" id="focus-mobile" maxlength="11"  type="tel" placeholder="请输入修改的手机号" />
							<i class="iconfont icon-guanbi1 is-null j-is-null"></i>
						</div>
					</div>
					<p class="fl t-remark">为了您的安全，我们会向你手机发送验证码</p>
					<input type="hidden" name="enabled_sms" value="1" />
					<button  id="next" class="btn-submit">下一步</button>
				
				</section>
			    
			</div>
			<div id="check" style="display:none">
			
				<section class="user-center user-forget-tel margin-lr">
					<p class="fl t-remark2">您的手机号：+86 <span  id="show_mobile" >111</span></p>
					<div class="text-all dis-box j-text-all" name="sms_codediv">
						<div class="input-text input-check  box-flex">
						    <input type="hidden" id="sms_code" name="sms_code" value="<?php echo $sms_code; ?>">
							<input class="j-input-text" type="text" name="sms_code" placeholder="请输入验证码" />
							<i class="iconfont icon-guanbi1 is-null j-is-null"></i>
						</div>
						<a type="button" id="sendsms" class="ipt-check-btn" href="#">获取验证码</a>
					</div>
					<input type="hidden" name="sms_signin" value="<?php echo $sms_signin; ?>" />
					<input type="hidden" name="u-h-forget" value="u-f-tel" />
					<button type="submit"  class="btn-submit">确定修改</button>
				</section>

			</div>
		</div>
		<div class="div-messages"></div>
		<!-- <?php } ?> -->
		<!-- <?php if($sms == 0) { ?> -->
			<section class="user-center margin-lr">
				<form action="<?php echo U('user/profile/user_edit_mobile');?>" method="post">
					<div class="text-all dis-box j-text-all">
						<div class="input-text box-flex">
							<input class="j-input-text mobile" id="focus"  maxlength="11" name="mobile" placeholder="<?php if($mobile) { ?><?php echo $mobile; ?><?php } else { ?>请输入手机号<?php } ?>" />
							<i class="iconfont icon-guanbi1 is-null j-is-null"></i>
						</div>
					</div>
					<button type="submit" class="btn-submit">确定修改</button>
				</form>
			</section>
		<!-- <?php } ?> -->
        <script>
        $(":input").keyup(function(){
	           var box=this.name+"div";
	           var div=$("div[name="+box+"]");
	           var value=div.attr("class").indexOf("active")
	           if ( value > 0 ){
	        	  div.removeClass("active");
	           }
		    });
		var time=60;
		var c=1;
		function data(){
				  if(time ==0 ){
					     c=1;
					     $("#sendsms").html("发送验证码");
					     time =60;
					     return;
				  } 
				  if(time != 0){
					     if($(".ipt-check-btn").attr("class").indexOf("disabled")<0){
                            $(".ipt-check-btn").addClass('disabled');
					     }
					     c=0; 
				    	 $("#sendsms").html("重新获取("+time+")");
				         time--;
				  }
				  setTimeout(data,1000);
		}
		
		$("#sendsms").click(function(){
			  if(c==0){ 
						 d_messages('发送频繁');
						 return;
			  }
		      var mobile     =$("#focus-mobile").val();
		      var myreg = /^(((13[0-9]{1})|(15[0-9]{1})|(18[0-9]{1}))+\d{8})$/; 
		      if( mobile==''){
		    	  d_messages('请输入手机号');
						mobile.focus(); 
						$("div[name=mobilediv]").addClass("active");
						return false;
		      }else if(!myreg.test(mobile)){
		    	  d_messages('请输入有效的手机号');
						     mobile.focus(); 
						     $("div[name=mobilediv]").addClass("active");
						     return false;
		    }
		    data();
		    ajax_mobile();
	         
		 })
        $(":input").keyup(function(){
            var box=this.name+"div";
            var div=$("div[name="+box+"]");
            var value=div.attr("class").indexOf("active")
            if ( value > 0 ){
         	  div.removeClass("active");
            }
 	    });
        $("#next").click(function(){
           var mobile = $("input[name=write_mobile]");
           var myreg = /^(((13[0-9]{1})|(15[0-9]{1})|(18[0-9]{1}))+\d{8})$/; 
           if(!myreg.test(mobile.val())) 
           { 
               mobile.focus();
               $("div[name=write_mobilediv]").addClass("active");
			   d_messages('请输入有效的手机号码！'); 
               return false; 
           }
           $("#show_mobile").text(mobile.val());
           $("input[name=mobile]").val(mobile.val());
           $("#show").css({display:"none"});
           $("#check").css({display:"block"}); 

        });
 
        function check(){
            var htmlcode=$("input[name=sms_code]");
            var returncode=$("input[name=return_code]").val();
            if( htmlcode.val() == ''){
            	htmlcode.focus();
            	$("div[name=sms_codediv]").addClass("active");
  			    d_messages('请输入验证码！'); 
  			    return false;
            }
            if( htmlcode.val() != returncode){
            	htmlcode.focus();
            	$("div[name=sms_codediv]").addClass("active");
  			    d_messages('验证码错误！');
    			return false; 
            }
            
        }
       
        //ajax发送验证码
        function ajax_mobile(){
        	 var mobile =$("#focus-mobile").val();
        	 var sms_code = $("#sms_code").val();
        	 $.post("<?php echo U('user/profile/send_sms');?>",{mobile:mobile,sms_code:sms_code},function(){
        		 
        	 },'json');
        }
        </script>
        <script>
        $("#next-email").click(function(){
        	$("#show-email").css({display:"none"});
            $("#check-email").css({display:"block"}); 
        }) 
        </script>
        <script>
        $("#focus").focus();
        $("#next-email").click(function(){
        	var name = $("#name").val();
        	var email = $("#ema").val();
        	var ema_code = $("#ema_code").val();
        	if(name == "" || email==""){
        		 d_messages('请输入验证码！',2); 
        	}
	       	$.post("<?php echo U('user/profile/send_email');?>",{name:name,emil:email,ema_code:ema_code},function(){

	    	},'json');
        	
        })
        </script>
        <script>
        $(".btn-submit").click(function(){
        	var mobile = $(".mobile");
        	var myreg = /^(((13[0-9]{1})|(15[0-9]{1})|(18[0-9]{1}))+\d{8})$/; 
        	if(!myreg.test(mobile.val())){
        		d_messages('请输入有效的手机号码！');
        		return false;
        	}
        	
        	
        })
        
        
        
        </script>
        
        
	</body>

</html>";s:12:"compile_time";i:1479717170;}";