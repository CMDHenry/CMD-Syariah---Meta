// JavaScript Document
function SetCookie(sName, sValue)
{
  date = new Date();
  document.cookie = sName + "=" + escape(sValue) + "; expires=" + date.toGMTString();
}

function DelCookie(sName)
{
  document.cookie = sName + "=" + escape(sValue) + "; expires=Fri, 31 Dec 1999 23:59:59 GMT;";
}

function GetCookie(sName)
{
  // cookies are separated by semicolons
  var aCookie = document.cookie.split("; ");

  for (var i=0; i < aCookie.length; i++)
  {
    // a name/value pair (a crumb) is separated by an equal sign
    var aCrumb = aCookie[i].split("=");
    if (sName == aCrumb[0]) 
      return unescape(aCrumb[1]);
  }

  // a cookie with the requested name does not exist
  return null;
}
