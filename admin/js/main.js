$(document).ready( function(){
	$("div.page_body").html("").hide();
	$("div#load").show();
	$("div.loginform_out").hide();
	
	function DecodeError(err, obj){
		res = "";
		switch (err) {
			case 'ERR_NotLogin': 
				res = "Please login";
				$(".loginform_out").show();
				// $('#loginform').show();	
				break;
			case 'ERR_InvalidName': res = "Invalid name: " + obj; break;
			case 'ERR_ExistName': res = "Exist name: " + obj; break;
			case 'ERR_NoneName': res = "Name does not exist: " + obj; break;
			case 'ERR_NoneEmpty': res = "None empty: " + obj; break;
			
		  default:
			if (obj != ""){
				obj = " (" + obj + ")";
			}
			res = err + " " + obj;
		}
		return res;
	}
	
	function loadFunctions(){

		/* + Закрывать при нажатии ESC */
		$(document).keyup(function(e) {
			if (e.keyCode === 27) {
				$('.overlay').css("display", 'none');
				$('.overlay_2').css("display", 'none');
			}
		});

		/* leftbar_menu_btn */
		$('.leftbar_menu_btn').click(function() {
			$('#left_menu').toggle(300);
		});

		/* + Скрыть overlay */
		$('.close').click(function() {
			$('.overlay').fadeOut(300);
			$('.overlay_2').fadeOut(300);
		});
	
		/* FOLDERS */

		/* + Показать Создание папки beki+ */
		$('.folder_add').on('click', function(){
			$('.folder_add_ul').fadeIn(300);
		});

		/* + Показать папки beki+ */
		$('.folder_open').click(function() {
			$(".pages_list").toggle(100);
			if ($("i.box_toggle", row).hasClass("fa-caret-up")){
				$("i.box_toggle", row).removeClass("fa-caret-up").addClass("fa-caret-down");
			}else if($("i.box_toggle", row).hasClass("fa-caret-down")){
				$("i.box_toggle", row).removeClass("fa-caret-down").addClass("fa-caret-up");
			};
		});
		
		/* + Показать Изменение папки beki+ */
		$('.folder_edit').click(function() {
			buff = $(this).parents("div.folder_title");
			name = $("span.name", buff).html();
			note = $("span.note", buff).html();
			$('div.folder_edit_ul [name="folder_name"]').val(name);
			$('div.folder_edit_ul [name="new_folder_name"]').val(name);
			$('div.folder_edit_ul [name="folder_note"]').val(note);
			$('.folder_edit_ul').fadeIn(300);
		});
	
		/* PAGES */
		/* + Показать Создание страницы */
		$('.page_add').on('click', function(){
			$('.page_add_ul').fadeIn(300);
		});
		
		/* + Показать Изменение страницы */
		$('.page_edit').click(function() {
			buff = $(this).parents("div.page_title");
			name = $("span.name", buff).html();
			note = $("span.note", buff).html();
			$('div.page_edit_ul [name="page_name"]').val(name);
			$('div.page_edit_ul [name="new_name"]').val(name);
			$('div.page_edit_ul [name="page_note"]').val(note);
			$('.page_edit_ul').fadeIn(300);
		});

		/*  + Показать Клонирование страницы */
		$('.page_clone').click(function() {
			buff = $(this).parents("div.page_title");
			name = $("span.name", buff).html();
			note = $("span.note", buff).html();
			$('div.page_clone_ul [name="page_name"]').val(name);
			$('div.page_clone_ul [name="new_name"]').val(name + "_copy");
			$('div.page_clone_ul [name="page_note"]').val(note);
			$('.page_clone_ul').fadeIn(300);
		});

		/* + Показать Удаление страницы */
		$('.page_del').click(function() {
			buff = $(this).parents("div.page_title");
			name = $("span.name", buff).html();
			$('div.page_del_ul [name="page_name"]').val(name);
			$('.page_del_ul').fadeIn(300);
		});
		
		/* + Просмотр страницы */
		$('.page_view').click(function() {
			buff = $(this).parents("div.page_title");
			name = $("span.name", buff).html();
			window.open("../?p=" + name, '_blank');
		});
		
		/* + Показать Добавление бокса */
		$('.page_box_add').click(function() {
			buff = $(this).parents("div.page_title");
			name = $("span.name", buff).html();
			$('div.page_box_add_ul input[name="page_name"]').val(name);
			$('.page_box_add_ul').fadeIn(300);
			
		});

		/* + Показать Изменение контейнера */
		$('.box_edit_btn').click(function() {
			buff = $(this).parents("div.box_header");
			name = $("span.name", buff).html();
			note = $("span.note", buff).html();
			page = $("div.page_title span.name").html();
			$('div.page_box_edit_ul [name="page_name"]').val(page);
			$('div.page_box_edit_ul [name="cont_name"]').val(name);
			$('div.page_box_edit_ul [name="new_name"]').val(name);
			$('div.page_box_edit_ul [name="cont_note"]').val(note);
			$('.page_box_edit_ul').fadeIn(300);
			
		});
		
		/* + Показать клонировать контейнер */
		$('.box_clone_btn').click(function() {
			buff = $(this).parents("div.box_header");
			name = $("span.name", buff).html();
			note = $("span.note", buff).html();
			page = $("div.page_title span.name").html();
			$('div.page_box_clone_ul input[name="page_name"]').val(page);
			$('div.page_box_clone_ul input[name="cont_name"]').val(name);
			$('div.page_box_clone_ul input[name="new_name"]').val(name + "_copy");
			$('div.page_box_clone_ul [name="cont_note"]').val(note);
			$.ajax({
				method: "GET",
				url: "../cic/admin.php?action=GetPages",
				success:
					function(data){
						list ="";
						if(data.err==""){
							i = 0;
					        while(i < data.res.length){
								if (data.res[i].name==page){sel =' selected="true"';}else{sel="";}
								list = list + '<option value="' + data.res[i].name + '"' + sel + ">" + data.res[i].name + "</option>";
								i = i + 1;
							}
						}else{
							list = data.err;
						}
						$('div.page_box_clone_ul select#page_list').html(list);
					}
			});
			$('div.page_box_clone_ul b[name="page_name"]').html(page);
			$('div.page_box_clone_ul b[name="cont_name"]').html(name);
			$('.page_box_clone_ul').fadeIn(300);
			
		});
		/* + Показать Удаление контейнера */
		$('.box_del').click(function() {
			buff = $(this).parents("div.box_header");
			name = $("span.name", buff).html();
			page = $("div.page_title span.name").html();
			$('div.page_box_del_ul [name="page_name"]').val(page);
			$('div.page_box_del_ul [name="cont_name"]').val(name);
			$('.page_box_del_ul').fadeIn(300);
		});
		/* + Показать Информацию о боксе */
		$('.box_info_btn').click(function() {
			buff = $(this).parents("div.box_header");
			name = $("span.name", buff).html();
			page = $("div.page_title span.name").html();
			if(name!=""){
				$.ajax({
					method: "GET",
					url: "../cic/admin.php?action=GetContainerInfo",
					data: {
						page: page, 
						container: name
					}, 
					success:
						function(data){
							if(data.err==""){
								editor = new JsonEditor('#container-info', data.res);
							}else{
								$("#container-info").html(data.err);
							}
						}
				});
				$.ajax({
					method: "GET",
					url: "../cic/admin.php?action=GetContainerVar",
					data: {
						page: page, 
						container: name
					}, 
					success:
						function(data){
							console.log(data);
							if(data.err==""){
								editor = new JsonEditor('#container-var', data.res);
							}else{
								$("#container-var").html(data.err);
							}
						}
				});
			}
			
			$('div.box_info_ul [name="page_name"]').val(page);
			$('div.box_info_ul [name="cont_name"]').val(name);
			$('.box_info_ul').fadeIn(100);
		});
		
		/* + Показать/Скрыть содержание бокса */
		$('.box_toggle').click(function() {
			row = $(this).parents(".row");
			row.children(".box_code").toggle(100);
			if ($("i.box_toggle", row).hasClass("fa-caret-up")){
				$("i.box_toggle", row).removeClass("fa-caret-up").addClass("fa-caret-down");
			}else if($("i.box_toggle", row).hasClass("fa-caret-down")){
				$("i.box_toggle", row).removeClass("fa-caret-down").addClass("fa-caret-up");
			};
		});
		
		
		/* ----- form buttons ---- */
		/* + Create page */
		$('div.page_add_ul .btn').click(function() {
			$('div.page_add_ul').fadeOut(300);
			name = $('div.page_add_ul [name="page_name"]').val();
			note = $('div.page_add_ul [name="page_note"]').val();
			if(name!=""){
				$.ajax({
					method: "POST",
					url: "../cic/admin.php?action=NewPage",
					data: {
						name: name, 
						note: note
					}, 
					success:
						function(data){
							if(data.err==""){
								location.reload();
							}else{
								$("div#error").show().html(DecodeError(data.err));
							}
						}
				});
			}
			$('div.page_add_ul [name="page_name"]').val("")
			$('div.page_add_ul [name="page_note"]').val("")
		});
		
		/* + Edit page info */
		$('div.page_edit_ul .btn').click(function() {
			$('div.page_edit_ul').fadeOut(300);
			name = $('div.page_edit_ul [name="page_name"]').val();
			newname = $('div.page_edit_ul [name="new_name"]').val();
			note = $('div.page_edit_ul [name="page_note"]').val();
			function SetNote(){
				$.ajax({
					method: "POST",
					url: "../cic/admin.php?action=SetPageNote",
					data: {
						page: newname, 
						note: note
					}, 
					success:
						function(data){
							if(data.err==""){
								location.reload();
							}else{
								$("div#error").show().html(DecodeError(data.err));
							}
						}
				});
			}
			if((name!="")&&(name!=newname)){
				$.ajax({
					method: "POST",
					url: "../cic/admin.php?action=RenamePage",
					data: {
						newname: newname, 
						page: name,
						note: note
					}, 
					success:
						function(data){
							if(data.err==""){
								SetNote();
							}else{
								$("div#error").show().html(DecodeError(data.err));
							}
						}
				});	
			}else{
				SetNote();
			}
			$('div.page_edit_ul [name="new_name"]').val("");
			$('div.page_edit_ul [name="page_name"]').val("");
			$('div.page_edit_ul [name="page_note"]').val("");
		});
		/* + clone page */
		$('div.page_clone_ul .btn').click(function() {
			$('div.page_clone_ul').fadeOut(300);
			page = $('div.page_clone_ul [name="page_name"]').val();
			name = $('div.page_clone_ul [name="new_name"]').val();
			note = $('div.page_clone_ul [name="page_note"]').val();
			if(name!=""){
				$.ajax({
					method: "POST",
					url: "../cic/admin.php?action=ClonePage",
					data: {
						page: page,
						name: name, 
						note: note
					}, 
					success:
						function(data){
							if(data.err==""){
								location.reload();
							}else{
								$("div#error").show().html(DecodeError(data.err));
							}
						}
				});
			}
			$('div.page_clone_ul [name="page_name"]').val("");
			$('div.page_clone_ul [name="new_name"]').val("");
			$('div.page_clone_ul [name="page_note"]').val("");
		});
		/* + delete page */
		$('div.page_del_ul .btn').click(function() {
			page = $('div.page_del_ul [name="page_name"]').val();
			$('div.page_del_ul').fadeOut(300);
			if(page!=""){
				$.ajax({
					method: "POST",
					url: "../cic/admin.php?action=DeletePage",
					data: {
						page: page
					}, 
					success:
						function(data){
							if(data.err==""){
								location.reload();
							}else{
								$("div#error").show().html(DecodeError(data.err));
							}
						}
				});
			}
			$('div.page_del_ul [name="page_name"]').val("");
		});
		/* + Create container */
		$('div.page_box_add_ul .btn').click(function() {
			$('div.page_box_add_ul').fadeOut(300);
			page = $('div.page_box_add_ul [name="page_name"]').val();
			name = $('div.page_box_add_ul [name="cont_name"]').val();
			note = $('div.page_box_add_ul [name="cont_note"]').val();
			if(name!=""){
				$.ajax({
					method: "POST",
					url: "../cic/admin.php?action=NewContainer",
					data: {
						page: page,
						name: name,
						note: note
					}, 
					success:
						function(data){
							if(data.err==""){
								$('a.page_menu[name='+ data.res +']').click();
							}else{
								$("div#error").show().html(DecodeError(data.err));
							}
						}
				});
			}
			$('div.page_box_add_ul [name="page_name"]').val("");
			$('div.page_box_add_ul [name="cont_name"]').val("");
			$('div.page_box_add_ul [name="cont_note"]').val("");
		});
		
		/* + Edit container */
		$('div.page_box_edit_ul .btn').click(function() {
			$('div.page_box_edit_ul').fadeOut(300);
			page = $('div.page_box_edit_ul [name="page_name"]').val();
			name = $('div.page_box_edit_ul [name="cont_name"]').val();
			note = $('div.page_box_edit_ul [name="cont_note"]').val();
			newname = $('div.page_box_edit_ul [name="new_name"]').val();
			if (newname!=""){
				function SetNote(){
					$.ajax({
						method: "POST",
						url: "../cic/admin.php?action=SetContainerNote",
						data: {
							page: page, 
							container: newname, 
							note: note
						}, 
						success:
							function(data){
								if(data.err==""){
									$('div.page_box_edit_ul [name="page_name"]').val("");
									$('div.page_box_edit_ul [name="cont_name"]').val("");
									$('div.page_box_edit_ul [name="cont_note"]').val("");
									$('div.page_box_edit_ul [name="new_name"]').val("");
									$('a.page_menu[name='+ data.res +']').click();
								}else{
									$("div#error").show().html(DecodeError(data.err));
								}
							}
					});
				}
				if(name!=newname){
					$.ajax({
						method: "POST",
						url: "../cic/admin.php?action=RenameContainer",
						data: {
							page: page,
							container: name,
							name: newname
						}, 
						success:
							function(data){
								if(data.err==""){
									SetNote();
								}else{
									$("div#error").show().html(DecodeError(data.err));
								}
							}
					});	
				}else{
					SetNote();
				}
			}
		});
		
		/* + Delete container */
		$('div.page_box_del_ul .btn').click(function() {
			$('div.page_box_del_ul').fadeOut(300);
			page = $('div.page_box_del_ul [name="page_name"]').val();
			name = $('div.page_box_del_ul [name="cont_name"]').val();
			if(name!=""){
				$.ajax({
					method: "POST",
					url: "../cic/admin.php?action=DeleteContainer",
					data: {
						page: page,
						container: name
					}, 
					success:
						function(data){
							if(data.err==""){
								$('a.page_menu[name='+ data.res +']').click();
							}else{
								$("div#error").show().html(DecodeError(data.err));
							}
						}
				});
			}
			$('div.page_box_del_ul [name="page_name"]').val("");
			$('div.page_box_del_ul [name="cont_name"]').val("");
		});
		
		
		/* + Clone container */
		$('div.page_box_clone_ul .btn').click(function() {
			$('div.page_box_clone_ul').fadeOut(300);
			page = $('div.page_box_clone_ul input[name="page_name"]').val();
			name = $('div.page_box_clone_ul input[name="cont_name"]').val();
			note = $('div.page_box_clone_ul [name="cont_note"]').val();
			newname = $('div.page_box_clone_ul input[name="new_name"]').val();
			newpage = $('div.page_box_clone_ul select#page_list').val();
			
			if(name!=""){
				$.ajax({
					method: "POST",
					url: "../cic/admin.php?action=CopyContainer",
					data: {
						page: page,
						name: name,
						newname: newname,
						newpage: newpage,
						note: note
					}, 
					success:
						function(data){
							if(data.err==""){
								$('a.page_menu[name='+ data.res +']').click();
							}else{
								$("div#error").show().html(DecodeError(data.err));
							}
						}
				});
			}
			$('div.page_box_clone_ul input[name="page_name"]').val("");
			$('div.page_box_clone_ul input[name="cont_name"]').val("");
			$('div.page_box_clone_ul [name="cont_note"]').val("");
			$('div.page_box_clone_ul input[name="new_name"]').val("");
			$('div.page_box_clone_ul select#page_list').val("");
		});
		
		/* - container info */
		$('div.box_info_ul .btn').click(function() {
			$('div.box_info_ul').fadeOut(300);
		});
		
		/* + save container content*/
		$('.box_save_btn').click(function() {
			row = $(this).parents(".row");
			content = $(".code", row).val();
			content = content.replace(/textareа/ig, "textarea");
			buff = $(this).parents("div.box_header");
			container = $("span.name", buff).html();
			page = $("div.page_title span.name").html();
			
			if(container!=""){
				$.ajax({
					method: "POST",
					url: "../cic/admin.php?action=SetContainerContent",
					data: {
						page: page,
						container: container,
						content: content
					}, 
					success:
						function(data){
							if(data.err==""){
								row = $(".row#"+container);
								btn = $('.box_save_btn', row)
								btn.removeClass("fa-save").addClass("fa-check-circle").attr("title","Saved");
							}else{
								$("div#error").show().html(DecodeError(data.err));
							}
						}
				});
			}
			
			
		});
		
		 
		// Codemirror
		$(".code").each(function (i, editorEl) {
			CodeMirror.fromTextArea(editorEl, {
				lineNumbers: true,
				matchBrackets: true,
				mode: "cicmode",
				//mode: "xml",
				theme: "darcula",
				autofocus: true,
				autoCloseTags: true
			}).on("change", function(cm) { /* Autosave on change */
				cm.save();
				row = $(cm.getTextArea()).parents(".row");
				$(".box_save_btn", row).removeClass("fa-check-circle").addClass("fa-save").attr("title","Save");
			});
		});
	}
	
	function OpenPage(name, note){
		$("div.page_body").html("").hide();
		$("div#load").show();
		$.ajax({
		  method: "GET",
		  url: "../cic/admin.php?action=GetContainers&page=" + name,
		  success:
			function(data){
				bodyPages =  "";
				if(data.err==""){
					i = 0;
					while(i < data.res.length){
						contentAgent = data.res[i].content;
						contentAgent = contentAgent.replace(/textarea/ig, "textareа");
						bodyPages =  bodyPages + 
						'<div id="'+data.res[i].name+'" class="row">'+
							'<div class="box_header">'+
								'<div class="box_title_left box_toggle"><i class="fas fa-code"></i>'+
								'<span class="name">'+data.res[i].name+'</span>'+
								'<span class="note">'+data.res[i].note+'</span></div>'+
								'<span class="box_title_right">'+
									'<i class="far fa-check-circle box_save_btn" title="Saved"></i>'+//fa-save
									'<i class="fas fa-exclamation-circle box_info_btn" title="Information"></i>'+
									'<i class="fas fa-pencil-alt box_edit_btn" title="Edit"></i>'+
									'<i class="fas fa-clone box_clone_btn" title="Copy"></i>'+
									'<i class="far fa-trash-alt box_del" title="Delete"></i>'+
									'<i class="fas fa-caret-down box_toggle" title="Show/Hide"></i>'+
								'</span>'+
							'</div>'+
							'<form action="#" class="box_code">'+
								'<textarea class="code" name="box_text" mode="text/html" value="">'+contentAgent+'</textarea>'+
							'</form>'+
						'</div>';
						i = i + 1;
					}
					$("div.page_body").html("").hide();
					bodyPages =   
						'<div class="page_title">'+
							'<div class="page_title_left"><i class="far fa-file-alt"></i>' +
								'<span class="name">'+name+'</span>'+
								'<span class="note">'+note+'</span>'+
							'</div>' + 
							'<span class="page_title_right">'+
								'<i class="fas fa-plus-circle page_box_add" title="Create container"></i>'+
								'<i class="fas fa-pencil-alt page_edit" title="Rename page"></i>'+
								'<i class="fas fa-clone page_clone" title="Copy page"></i>'+
								'<i class="far fa-trash-alt page_del" title="Delete page"></i>'+
								'<i class="far fa-eye page_view" title="View page"></i>'+
							'</span>'+
						'</div>' + 
						bodyPages;
					$("div#load").hide();
					$("div#page").show().html(bodyPages);
					//$(".box_code").height("");
					$("textarea").height($("textarea")[0].scrollHeight);
					loadFunctions();
					$(".box_code").toggle(100);
					window.scrollTo(0, 0);
				}else{
					$("div#load").hide();
					$("div#error").show().html(DecodeError(data.err));
				}
			}
		});
	}
	
	function LoadPages(){
		menuPages = "";
		bodyPages = "";
		$.ajax({
		  method: "GET",
		  url: "../cic/admin.php?action=GetPages",
		  success:
			function(data){
				if(data.err==""){
					i = 0;
					while(i < data.res.length){
						menuPages = menuPages + '<li><a name="'+data.res[i].name+'" id="'+data.res[i].id+'" note="'+data.res[i].note+'" class="page_menu" href="#"><i class="fas fa-file-alt"></i>' + data.res[i].name + '</a></li>';
						
						bodyPages =  bodyPages + 
						'<div class="page_title">'+
							'<div class="page_title_left">' +
								'<a name="'+ data.res[i].name +
									'" id="'+ data.res[i].id +
									'" parent="'+ data.res[i].parent +
									'" note="'+ data.res[i].note +
									'" class="page_menu" title="Created: [' + data.res[i].insdate + ']\nChanged:[' + data.res[i].update + ']" href="#"><i class="fas fa-file-alt"></i>'+
								'<span class="name">'+data.res[i].name+'</span></a>'+
								'<span class="note">'+data.res[i].note+'</span>'+
							'</div>'+
							'<span class="page_title_right">'+
								'<i class="fas fa-pencil-alt page_edit" title="Edit name"></i>'+
								'<i class="fas fa-clone page_clone" title="Copy page"></i>'+
								'<i class="far fa-trash-alt page_del" title="Delete page"></i>'+
								'<i class="far fa-eye page_view" title="View page"></i>'+
							'</span>'+
						'</div>';
						
						i = i + 1;
					}
					menuPages = menuPages + '<li><a href="#" class="page_add"><i class="fas fa-plus-circle"></i>New Page</a></li>';
					$("ul#pages_list").html(menuPages);
					$("div#load").hide();
					$("div#project").show().html(bodyPages);
					loadFunctions();
					$('a.page_menu').click(function() {
						name = $(this).attr('name');
						note = $(this).attr('note');
						OpenPage(name, note);
					});
				}else{
					$("ul#pages_list").empty();
					$("div#load").hide();
					$("div#error").show().html(DecodeError(data.err));
				}
			}
		});
	}
	
	LoadPages();
	
});