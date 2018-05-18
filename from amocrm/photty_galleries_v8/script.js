define(['jquery'], function($){
	var id_lead;

    var CustomWidget = function () {
    	var self = this;
    	system = self.system();
    	console.log(system);
		this.callbacks = {
			render: function(){
				setInterval(showBtn,1000);
				return true;
			},
			init: function(){
				return true;
			},
			bind_actions: function(){
				return true;
			},
			settings: function(){
				//get data from api
				//add a view
				return true;
			},
			onSave: function(){
				//send to api
				return true;
			},
			destroy: function(){
				
			}
		};
		return this;
    };

    $('body').off('click','.create-gallery');

    $('body').on('click','.create-gallery', function(e){
    	e.preventDefault();
    	console.log('create '+id_lead);
    	$.get('/private/api/v2/json/leads/list',{id:id_lead},function(data){
    		$.get('/private/api/v2/json/contacts/links',{deals_link:[data.response.leads[0].id]},function(data3){
    			var id = [];
    			for(var i in data3.response.links)
    				id.push(data3.response.links[i].contact_id);
    		$.get('/private/api/v2/json/contacts/list',{id:id},function(data2){
	    		console.log(data);
	    		console.log(data2);
	    		var f = data.response.leads[0].custom_fields;
	    		var link = '';
	    		for(var i in f)
	    			if(f[i].id=='1963951')
	    			{
	    				link = f[i].values[0].value;
	    				break;
	    			}

	    		var link2 = '';
	    		for(var i in f)
	    			if(f[i].id=='1964109')
	    			{
	    				link2 = f[i].values[0].value;
	    				break;
	    			}

	    		if(link=='')
	    		{
	    			alert('Ссылка на галерею пуста');
	    			return false;
	    		}

	    		var name = [];

	    		for(var i in data2.response.contacts)
	    		{
	    			name.push(data2.response.contacts[i].name.split(' ')[0]);
	    		}

	    		name = name.join(', ');

	    		var title = data.response.leads[0].name.toLowerCase();

	    		for(var i in f)
	    			if(f[i].id=='1964107')
	    			{
	    				title = f[i].values[0].value.toLowerCase();
	    				break;
	    			}

	    		var custom = ['','','','','','','','','','','','','','','','','','','',''];

	    		for(var i in f)
	    			if(f[i].id=='1963951')
	    			{
	    				var val = [];
	    				for(var t in f[i].values)
	    					val.push(f[i].values[t].value);
	    				custom[0] = val.join(', ');
	    				break;
	    			}

	    		for(var i in f)
	    			if(f[i].id=='1964109')
	    			{
	    				var val = [];
	    				for(var t in f[i].values)
	    					val.push(f[i].values[t].value);
	    				custom[1] = val.join(', ');
	    				break;
	    			}

	    		for(var i in f)
	    			if(f[i].id=='1964107')
	    			{
	    				var val = [];
	    				for(var t in f[i].values)
	    					val.push(f[i].values[t].value);
	    				custom[2] = val.join(', ');
	    				break;
	    			}

	    		for(var i in f)
	    			if(f[i].id=='1589102')
	    			{
	    				var val = [];
	    				for(var t in f[i].values)
	    					val.push(f[i].values[t].value);
	    				custom[3] = val.join(', ');
	    				break;
	    			}

	    		for(var i in f)
	    			if(f[i].id=='1939385')
	    			{
	    				var val = [];
	    				for(var t in f[i].values)
	    					val.push(f[i].values[t].value);
	    				custom[4] = val.join(', ');
	    				break;
	    			}

	    		for(var i in f)
	    			if(f[i].id=='1939387')
	    			{
	    				var val = [];
	    				for(var t in f[i].values)
	    					val.push(f[i].values[t].value);
	    				custom[5] = val.join(', ');
	    				break;
	    			}

	    		for(var i in f)
	    			if(f[i].id=='1944701')
	    			{
	    				var val = [];
	    				for(var t in f[i].values)
	    					val.push(f[i].values[t].value);
	    				custom[6] = val.join(', ');
	    				break;
	    			}

	    		for(var i in f)
	    			if(f[i].id=='1949159')
	    			{
	    				var val = [];
	    				for(var t in f[i].values)
	    					val.push(f[i].values[t].value);
	    				custom[7] = val.join(', ');
	    				break;
	    			}

	    		for(var i in f)
	    			if(f[i].id=='1589114')
	    			{
	    				var val = [];
	    				for(var t in f[i].values)
	    					val.push(f[i].values[t].value);
	    				custom[8] = val.join(', ');
	    				break;
	    			}

	    		for(var i in f)
	    			if(f[i].id=='1589100')
	    			{
	    				var val = [];
	    				for(var t in f[i].values)
	    					val.push(f[i].values[t].value);
	    				custom[9] = val.join(', ');
	    				break;
	    			}

	    		for(var i in f)
	    			if(f[i].id=='1589104')
	    			{
	    				var val = [];
	    				for(var t in f[i].values)
	    					val.push(f[i].values[t].value);
	    				custom[10] = val.join(', ');
	    				break;
	    			}

	    		for(var i in f)
	    			if(f[i].id=='1847312')
	    			{
	    				var val = [];
	    				for(var t in f[i].values)
	    					val.push(f[i].values[t].value);
	    				custom[11] = val.join(', ');
	    				break;
	    			}

	    		for(var i in f)
	    			if(f[i].id=='1939899')
	    			{
	    				var val = [];
	    				for(var t in f[i].values)
	    					val.push(f[i].values[t].value);
	    				custom[12] = val.join(', ');
	    				break;
	    			}

	    		for(var i in f)
	    			if(f[i].id=='1847338')
	    			{
	    				var val = [];
	    				for(var t in f[i].values)
	    					val.push(f[i].values[t].value);
	    				custom[13] = val.join(', ');
	    				break;
	    			}

	    		for(var i in f)
	    			if(f[i].id=='1939897')
	    			{
	    				var val = [];
	    				for(var t in f[i].values)
	    					val.push(f[i].values[t].value);
	    				custom[14] = val.join(', ');
	    				break;
	    			}

	    		for(var i in f)
	    			if(f[i].id=='1847352')
	    			{
	    				var val = [];
	    				for(var t in f[i].values)
	    					val.push(f[i].values[t].value);
	    				custom[15] = val.join(', ');
	    				break;
	    			}

	    		for(var i in f)
	    			if(f[i].id=='1589106')
	    			{
	    				var val = [];
	    				for(var t in f[i].values)
	    					val.push(f[i].values[t].value);
	    				custom[16] = val.join(', ');
	    				break;
	    			}

	    		for(var i in f)
	    			if(f[i].id=='1589108')
	    			{
	    				var val = [];
	    				for(var t in f[i].values)
	    					val.push(f[i].values[t].value);
	    				custom[17] = val.join(', ');
	    				break;
	    			}

	    		for(var i in f)
	    			if(f[i].id=='1589110')
	    			{
	    				var val = [];
	    				for(var t in f[i].values)
	    					val.push(f[i].values[t].value);
	    				custom[18] = val.join(', ');
	    				break;
	    			}

	    		for(var i in f)
	    			if(f[i].id=='1589112')
	    			{
	    				var val = [];
	    				for(var t in f[i].values)
	    					val.push(f[i].values[t].value);
	    				custom[19] = val.join(', ');
	    				break;
	    			}

	    		var ds = '';
	    		for(var i in f)
	    			if(f[i].id=='1589100')
	    			{
	    				var val = [];
	    				for(var t in f[i].values)
	    					val.push(f[i].values[t].value);
	    				ds = val.join(', ');
	    				break;
	    			}

	    		if(ds!='')
	    		{
	    			ds = ds.split(' ');
	    			ds = ds[0];
	    			ds = ds.split('-');
	    			ds = ds[2]+'.'+ds[1]+'.'+ds[0];
	    		}

	    		custom = JSON.stringify(custom);

	    		var id = data.response.leads[0].id;

	    		$.post('https://photty.ru/api/photty_galleries/gallery.php',{title_lead:data.response.leads[0].name,title:title,name:name,link:link,link2:link2, id:data.response.leads[0].id, custom:custom},function(data){
	    			$('.feed-note__button_cancel').click()
	    			$('.feed-compose-switcher__text').click()
	    			$('.js-switcher-email').click()
	    			console.log(data);
	    			$.post('/private/api/v2/json/leads/set',{request:{leads:{update:[{id:id,last_modified:parseInt((new Date).getTime()/1000),custom_fields:[{id:1944701,values:[{value:data}]}]}]}}})
	    			setTimeout("$('.js-compose-mail-templates_field-container button').click()",1000);
	    			setTimeout("$('.control--select--list--item[data-value=48285]').click()",1500);
	    			console.log(data);
	    			//var fn = $('.feed-compose_mail__select button span').eq(0).html().split(' ')[0];
	    			//console.log(fn);
	    			//setTimeout("$('.feed-compose__message-area').val($('.feed-compose__message-area').val().split('{{contact.fname}}').join('"+fn+"'));",5000);
	    			//setTimeout("$('.feed-compose__message-area').val($('.feed-compose__message-area').val().split('{{contact.fname}}').join('"+fn+"'));",5500);
	    			
	    			setTimeout("$('.feed-compose__message-area').val($('.feed-compose__message-area').val().split('{{link}}').join('"+data+"'));",2000);
	    			setTimeout("$('.feed-compose__message-area').val($('.feed-compose__message-area').val().split('{{link}}').join('"+data+"'));",2500);

	    			setTimeout("$('.feed-compose__message-area').val($('.feed-compose__message-area').val().split('{{Дата съемки}}').join('"+ds+"'));",3000);
	    			setTimeout("$('.feed-compose__message-area').val($('.feed-compose__message-area').val().split('{{Дата съемки}}').join('"+ds+"'));",3500);

	    			setTimeout("$('.text-input[name=subject]').val($('.text-input[name=subject]').val().split('{{Дата съемки}}').join('"+ds+"'));",4000);
	    			setTimeout("$('.text-input[name=subject]').val($('.text-input[name=subject]').val().split('{{Дата съемки}}').join('"+ds+"'));",4500);

	    			//setTimeout("$('.text-input[name=subject]').val($('.text-input[name=subject]').val().split('{{contact.fname}}').join('"+fn+"'));",6000);
	    			//setTimeout("$('.text-input[name=subject]').val($('.text-input[name=subject]').val().split('{{contact.fname}}').join('"+fn+"'));",6500);
	    		})
	    	})
    		})
    	})
    })

 	$('body').off('click','.del-gallery');

    $('body').on('click','.del-gallery', function(e){
    	e.preventDefault();
    	//post query to php with id and link
    	$.get('/private/api/v2/json/leads/list',{id:id_lead},function(data){
    		var id,link;
    		for(var i in data.response.leads[0].custom_fields)
    		{
    			var v = data.response.leads[0].custom_fields[i];
    			if(v.id=='1944701')
    				id = v.values[0].value.split('https://photty.ru/gallery/').join('').split('/').join('');
    			if(v.id=='1963951')
    				link = v.values[0].value.split('https://yadi.sk/d/').join('');
    		}
    		alert('Процесс удаления запущен');
			$.post('https://photty.ru/api/photty_galleries/del_gallery.php',{id:id,link:link,id_lead:id_lead},function(data){
				console.log(data);
			})
			//update lead
			$.post('/private/api/v2/json/leads/set',{request:{leads:{update:[{id:id_lead,last_modified:parseInt((new Date).getTime()/1000),custom_fields:[{id:1944701,values:[{value:''}]}]}]}}})
    	})
    })

return CustomWidget;

function showBtn()
{
	id_lead = $('*[data-id=lead_id] span').html().substr(1);
	if($('*[data-card-active] .create-gallery').length<1)
	{
		$('.card-entity-form__top').append('<button class="button-input button-input_blue create-gallery" style="margin-top: 20px;">Создать галерею(обновить)</button>');
		$('.card-entity-form__top').append('<button class="button-input button-input_blue del-gallery" style="margin-top: 20px;">Удалить галерею</button>');
	}
}

});





