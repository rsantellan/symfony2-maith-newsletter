function bringEditProfile(url)
{
  $.ajax({
    url: url,
    type: 'get',
    dataType: 'json',
    success: function(json){
      $('#edit_profile_container').html(json.view);
    }, 
    complete: function()
    {

    }
  });

  return false;
}

function saveProfileEditData(form)
{
  $.ajax({
    url: $(form).attr('action'),
    data: $(form).serialize(),
    type: 'post',
    dataType: 'json',
    success: function(json){
      if(json.result == 'OK')
      {
        $('#profile_information_data').html(json.viewshow);
      }
      else
      {
        $('#edit_profile_container').html(json.view);
      }
              
    }
    , 
    complete: function()
    {

    }
  });
  return false;
}

function docheckMail(url)
{
  console.log(url);
  $.ajax({
    url: url,
    type: 'get',
    dataType: 'json',
    success: function(json){
      if(json.isvalid || json.isvalid == 'true')
      {
          toastr.info(json.message);
      }
      else
      {
          toastr.error(json.message);
      }
      
    }, 
    complete: function()
    {

    }
  });

  return false;
}