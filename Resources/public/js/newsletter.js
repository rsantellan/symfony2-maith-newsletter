function addToUrlLayout(element)
{
  $(element).attr('href', $(element).attr('basehref') + '?layout='+$('#emaillayouts').val());
  
}

function retrieveTableNewsletter(pagerUrl)
{
  $.blockUI();
  $.ajax({
      url: pagerUrl,
      cache: false,
      success: function(data){
        $('#tableNewsletterContainer').html(data.html);
        
        
      }, 
      complete: function()
      {
        $.unblockUI();
      }
  });
  return false; 
}

function addSimpleUser(form)
{
  $.blockUI();
  $.ajax({
      url: $(form).attr('action'),
      data: $(form).serialize(),
      type: 'post',
      dataType: 'json',
      success: function(data){
        if(data.result == 'true' || data.result == true)
        {
            toastr.info(data.message);
        }
        else
        {
            toastr.error(data.message);
        }
        $('#new_user_container').html(data.html);
      }, 
      complete: function()
      {
        $.unblockUI();
      }
  });
  return false;
}

function searchUser(form)
{
  $.blockUI();
  $.ajax({
      url: $(form).attr('action'),
      data: $(form).serialize(),
      type: 'post',
      dataType: 'json',
      success: function(data){
        $('#newsletter-global-container').fadeOut('slow');    
        $('#newsletter-compose-container').html(data.html);  
      }, 
      complete: function()
      {
        $.unblockUI();
      }
  });
  return false;
}

function addGroup(form)
{
  $.blockUI();
  $.ajax({
      url: $(form).attr('action'),
      data: $(form).serialize(),
      type: 'post',
      dataType: 'json',
      success: function(data){
        if(data.result == 'true' || data.result == true)
        {
            toastr.info(data.message);
            $('#groups_table_body').append(data.listhtml);
        }
        else
        {
            toastr.error(data.message);
        }
        $('#new_group_container').html(data.html);
      }, 
      complete: function()
      {
        $.unblockUI();
      }
  });
  return false;
}

function editGroup(element)
{
  $('#newsletter-global-container').fadeOut('slow');  
  $.blockUI();
  $.ajax({
      url: $(element).attr('href'),
      cache: false,
      success: function(data){
        $('#newsletter-compose-container').html(data.html);
        setTimeout(function(){
            startUserSelector();
          }, 200);
      }, 
      complete: function()
      {
        $.unblockUI();
      }
  });
  return false; 
}

function saveEditGroup(form)
{
  $.blockUI();
  $.ajax({
      url: $(form).attr('action'),
      data: $(form).serialize(),
      type: 'post',
      dataType: 'json',
      success: function(data){
        if(data.result == 'true' || data.result == true)
        {
            toastr.info(data.message);
            $('#group_row_'+data.id).replaceWith(data.html);
        }
        else
        {
            toastr.error(data.message);
        }
      }, 
      complete: function()
      {
        $.unblockUI();
      }
  });
  return false;
}

function addUserToGroup(form)
{
  $.blockUI();
  $.ajax({
      url: $(form).attr('action'),
      data: $(form).serialize(),
      type: 'post',
      dataType: 'json',
      success: function(data){
        if(data.result == 'true' || data.result == true)
        {
            toastr.info(data.message);
            $('#first_user_group_container').append(data.html);
            $('#users-selector').val('');
        }
        else
        {
            toastr.error(data.message);
        }
      }, 
      complete: function()
      {
        $.unblockUI();
      }
  });
  return false;
}

function activeDisableUser(element)
{
  $.blockUI();
  $.ajax({
      url: $(element).attr('href'),
      type: 'post',
      dataType: 'json',
      success: function(data){
        if(data.result == 'true' || data.result == true)
        {
            toastr.info(data.message);
            $('#user_list_row_'+data.id).replaceWith(data.html);
        }
        else
        {
            toastr.error(data.message);
        }
      }, 
      complete: function()
      {
        $.unblockUI();
      }
  });
  return false;
}

function removeGroup(element, question)
{
  if(!confirm(question))  
  {
      return;
  }
  $.blockUI();
  $.ajax({
      url: $(element).attr('href'),
      type: 'post',
      dataType: 'json',
      success: function(data){
        if(data.result == 'true' || data.result == true)
        {
            toastr.info(data.message);
            $('#newsletter-global-container').fadeIn('slow');
            $('#newsletter-compose-container').html('');
            $('#group_row_'+data.id).remove();
        }
        else
        {
            toastr.error(data.message);
        }
      }, 
      complete: function()
      {
        $.unblockUI();
      }
  });
  return false;
}

function removeUserGroup(element, question)
{
  if(!confirm(question))  
  {
      return;
  }
  $.blockUI();
  $.ajax({
      url: $(element).attr('href'),
      type: 'post',
      dataType: 'json',
      success: function(data){
        if(data.result == 'true' || data.result == true)
        {
            toastr.info(data.message);
            $('#user_of_group_'+data.userId+'_'+data.groupId).remove();
        }
        else
        {
            toastr.error(data.message);
        }
      }, 
      complete: function()
      {
        $.unblockUI();
      }
  });
  return false;
}

function createEditNewsletter(element)
{
  $('#newsletter-global-container').fadeOut('slow');  
  $.blockUI();
  $.ajax({
      url: $(element).attr('href'),
      cache: false,
      success: function(data){
        $('#newsletter-compose-container').html(data.html);
        setTimeout(function(){
          startComposerTinyMCE();
          $(".datepicker").datepicker();
        }, 200);
        
      }, 
      complete: function()
      {
        $.unblockUI();
      }
  });
  return false; 
}

function createEditEmailLayout(element)
{
  $('#newsletter-global-container').fadeOut('slow');  
  $.blockUI();
  $.ajax({
      url: $(element).attr('href'),
      cache: false,
      success: function(data){
        $('#newsletter-compose-container').html(data.html);
        setTimeout(function(){
          startComposerTinyMCE();
          $(".datepicker").datepicker();
        }, 200);
        
      }, 
      complete: function()
      {
        $.unblockUI();
      }
  });
  return false; 
}

function saveEmailLayoutData(form)
{
  tinyMCE.triggerSave();
  $.blockUI();
  $.ajax({
      url: $(form).attr('action'),
      data: $(form).serialize(),
      type: 'post',
      dataType: 'json',
      success: function(data){
        if(data.result == 'true' || data.result == true)
        {
            toastr.info(data.message);
            if(data.isupdate != undefined && (data.isupdate == 'true' || data.isupdate == true))
            {
              $('#email_layout_row_'+data.id).replaceWith(data.listHtml);
            }
            else
            {
              if(data.listHtml != undefined)
              {
                $('#email_layout_table_body').append(data.listHtml);
              }  
            }
            
            $('#newsletter-global-container').fadeIn('slow');
            $('#newsletter-compose-container').html('');
        }
        else
        {
            toastr.error(data.message);
        }
        $('#newsletter-compose-container').html(data.html);
      }, 
      complete: function()
      {
        $.unblockUI();
      }
  });
  return false;
}

function saveData(form)
{
  tinyMCE.triggerSave();
  $.blockUI();
  $.ajax({
      url: $(form).attr('action'),
      data: $(form).serialize(),
      type: 'post',
      dataType: 'json',
      success: function(data){
        if(data.result == 'true' || data.result == true)
        {
            toastr.info(data.message);
            if(data.listHtml != undefined)
            {
              $('#newsletter_body_container').append(data.listHtml);
            }
        }
        else
        {
            toastr.error(data.message);
        }
        $('#newsletter-compose-container').html(data.html);
        $('#newsletter-global-container').fadeIn('slow');
        $('#newsletter-compose-container').html('');
      }, 
      complete: function()
      {
        $.unblockUI();
      }
  });
  return false;
}


function showChangesTypes(combo)
{
  var comboValue = $(combo).val();
  if(comboValue == 1)
  {
    $('#selector-container').html('');
    $('#maith_newsletterbundle_contentsend_sendlist').val('');
  }
  if(comboValue == 2)
  {
    $.blockUI();
    $.ajax({
        url: $('#selector-group-to-send').val(),
        cache: false,
        success: function(data){
          $('#selector-container').html(data.html);
          $('#maith_newsletterbundle_contentsend_sendlist').val('');
        }, 
        complete: function()
        {
          $.unblockUI();
        }
    });
  }
  if(comboValue == 3)
  {
    $.blockUI();
    $.ajax({
        url: $('#newsletter-user-form').val(),
        cache: false,
        success: function(data){
          $('#selector-container').html(data.html);
          $('#maith_newsletterbundle_contentsend_sendlist').val('');
          setTimeout(function(){
            startUserSelector();
          }, 200);
        }, 
        complete: function()
        {
          $.unblockUI();
        }
    });
  }
  //console.info(comboValue);
}

function startUserSelector()
{
   $( "#users-selector" )
      // don't navigate away from the field on tab when selecting an item
      .bind( "keydown", function( event ) {
            if ( event.keyCode === $.ui.keyCode.TAB && $( this ).autocomplete( "instance" ).menu.active ) {
                event.preventDefault();
            }
      })
      .autocomplete({
          source: function( request, response ) {
            $.getJSON( $('#newsletter-user-autocomplete').val(), {
              term: extractLast( request.term )
            }, response );
          },
          search: function() {
            // custom minLength
            var term = extractLast( this.value );
            if ( term.length < 2 ) {
              return false;
            }
          },
          focus: function() {
            // prevent value inserted on focus
            return false;
          },
          select: function( event, ui ) {
            var terms = split( this.value );
            // remove the current input
            terms.pop();
            // add the selected item
            terms.push( ui.item.value );
            // add placeholder to get the comma-and-space at the end
            terms.push( "" );
            this.value = terms.join( ", " );
            return false;
          }
    });
}

function split( val ) {
  return val.split( /,\s*/ );
}
function extractLast( term ) {
  return split( term ).pop();
}

function saveSendData(form)
{
  $.blockUI();
  if($('#maith_newsletterbundle_contentsend_sendToType').val() == 2)
  {
    $('#maith_newsletterbundle_contentsend_sendlist').val($('#groups-selector').val());
  }
  if($('#maith_newsletterbundle_contentsend_sendToType').val() == 3)
  {
    $('#maith_newsletterbundle_contentsend_sendlist').val($('#users-selector').val());
    var idsOfGroups = [];
    if($('#group-users-container').length > 0)
    {
        $('#group-users-container input:checkbox:checked').each(function(index, value){ 
            idsOfGroups.push($(value).val());
        });
        
        $('#maith_newsletterbundle_contentsend_sendlistIds').val(idsOfGroups.join(','));
    }
  }
  
  $.ajax({
      url: $(form).attr('action'),
      data: $(form).serialize(),
      type: 'post',
      dataType: 'json',
      success: function(data){
        if(data.result == 'true' || data.result == true)
        {
            toastr.info(data.message);
            $('#table_body_not_sended_rows').prepend(data.html);
        }
        else
        {
            toastr.error(data.message);
        }
      }, 
      complete: function()
      {
        $.unblockUI();
      }
  });
  return false;
}

function removeSendedContent(element, message)
{
  if(confirm(message))
  {
      $.blockUI();
      $.ajax({
          url: $(element).attr('href'),
          type: 'post',
          dataType: 'json',
          success: function(data){
            if(data.result == 'true' || data.result == true)
            {
                toastr.info(data.message);
                $('#sended_row_'+data.id).fadeOut('slow', function(){
                  $(this).remove();
                });
            }
            else
            {
                toastr.error(data.message);
            }
            //$('#newsletter-compose-container').html(data.html);


          }, 
          complete: function()
          {
            $.unblockUI();
          }
      });
  }
  return false; 
}

function startComposerTinyMCE()
{
  console.log('startComposerTinyMCE');
  try{
    tinymce.remove();
  }catch(e)
  {
    console.info('no tiny');
  }
  
  tinyMCE.init({
	  // General options
	  mode : "textareas",
    editor_selector: 'mceEditor',
	  theme: "modern",
    relative_urls : false,
    remove_script_host : false,
    convert_urls : true,
	  plugins: [
		  "advlist autolink lists link image charmap print preview hr anchor pagebreak",
		  "searchreplace wordcount visualblocks visualchars code fullscreen",
		  "insertdatetime media nonbreaking save table contextmenu directionality",
		  "emoticons template paste textcolor responsivefilemanager"
	  ],
	  toolbar1: "insertfile undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image",
	  toolbar2: "print preview media | forecolor backcolor emoticons responsivefilemanager",
    image_advtab: true,
	  forced_root_block : "",
	  force_br_newlines : true,
	  force_p_newlines : false,
    external_filemanager_path:"/bundles/maithcommonadmin/filemanager/",
    filemanager_title:"Responsive Filemanager" ,
    external_plugins: { "filemanager" : "/bundles/maithcommonadmin/filemanager/plugin.min.js"}

  });  
}