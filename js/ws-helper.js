function enblResend (){
  $("#otp-rsd-click").prop("disabled", false);
}
function dsblResend (){
  $("#otp-rsd-click").prop("disabled", true);
}
function otpResent(resp) {
  if( resp && ( resp.errors.length <= 0 || resp.status == "0.0") ){
    $("#res-cnt-view").fadeIn();
    dsblResend();
    minuteTimer(12 * 60,"#cnt-timer", enblResend);
  }
}
const helpr_rsc = (path, callback, rq = {}, opt = {type : "GET", data_type : "json", processData:true}, errorcb) => {
  let dfOpt = {
    type: "GET",
    data_type: "json",
    processData: true
  }
  if (typeof opt.processData !== undefined) dfOpt.processData = opt.processData == true;
  if (path && typeof callback == "function") {
    $.ajax({
      url :  path,
      dataType : opt.data_type !== undefined ? opt.data_type : dfOpt.data_type,
      type : (opt.type !== undefined && ["GET","POST"].includes(opt.type)) ? opt.type : dfOpt.type,
      data : rq,
      success : function(resp) {
        if (dfOpt.processData) {
          if( resp && (resp.status == '0.0' || resp.errors.length <= 0) ){
            let dt = "data" in resp ? resp.data : resp;
            callback(dt);
          } else {
            if (errorcb) {
              errorcb(resp.status, `${resp.message}: ${resp.errors.join('<br>')}`);
            } else {
              if (resp.errors.length) console.error(`Invalid response: ${resp.errors.join(" | ")}`);
            }
          }
        } else {
          callback(resp);
        }
      },
      error : function(xhr){
        if (errorcb) {
          // errorcb(xhr.status, xhr.statusText);
          errorcb(xhr.status, xhr.responseText, xhr.statusText);
        } else {
          console.error(`Failed to load requested recources: ${xhr.responseText}`);
        }
      }
    });
  }
}

const cryptos = {
  BTC : "Bitcoin",
  ETH : "Ethereum",
  BNB : "Binance Coin",
  SOL : "Solana",
  ADA : "Cardano",
  DOGE : "Dogecoin",
  XRP : "XRP"
}
const decCount = (curr) => {
  return cryptos.hasOwnProperty(curr)
    ? 8
    : 2;
}
const recur_terms = {
  OFF : "One-off",
  DAILY : "Daily",
  WEEKLY : "Weekly",
  MONTHLY : "Monthly",
  QUARTERLY : "Every 3 Months",
  BIANNUAL : "Every 6 Months",
  YEARLY : "Yearly",
  BIYEARLY : "Every 2 Years"
}

const color_theme = {
  native           : { title : "Native (Catali)", background : "#1976D2", foreground : "#ffffff" },
  gold             : { title : "Gold",            background : "#EBBD63", foreground : "#000" },
  "rose-gold"      : { title : "Rose Gold",       background : "#FDD09F", foreground : "#000" },
  red              : { title : "Red",             background : "#F44336", foreground : "#ffffff" },
  blue             : { title : "Blue",            background : "#2196F3", foreground : "#ffffff" },
  "light-blue"     : { title : "Light Blue",      background : "#03A9F4", foreground : "#ffffff" },
  "midnight-blue"  : { title : "Midnight Blue",   background : "#2c3e50", foreground : "#ffffff" },
  "blue-grey"      : { title : "Blue Grey",       background : "#607D8B", foreground : "#ffffff" },
  green            : { title : "Green",           background : "#4CAF50", foreground : "#ffffff" },
  "nigeria-green"  : { title : "Nigeria Green",   background : "#008751", foreground : "#ffffff" },
  "light-green"    : { title : "Light Green",     background : "#8BC34A", foreground : "#000" },
  "green-sea"      : { title : "Green Sea",       background : "#16a085", foreground : "#ffffff" },
  yellow           : { title : "Yellow",          background : "#FFEB3B", foreground : "#000" },
  amber            : { title : "Amber",           background : "#FFC107", foreground : "#000" },
  asphalt          : { title : "Asphalt",         background : "#34495e", foreground : "#ffffff" },
  pink             : { title : "Pink",            background : "#E91E63", foreground : "#ffffff" },
  purple           : { title : "Purple",          background : "#9C27B0", foreground : "#ffffff" },
  "deep-purple"    : { title : "Deep Purple",     background : "#673AB7", foreground : "#ffffff" },
  olive            : { title : "Olive",           background : "#808000", foreground : "#ffffff" },
  indigo           : { title : "Indigo",          background : "#3F51B5", foreground : "#ffffff" },
  cyan             : { title : "Cyan",            background : "#00BCD4", foreground : "#ffffff" },
  teal             : { title : "Teal",            background : "#009688", foreground : "#ffffff" },
  lime             : { title : "Lime",            background : "#CDDC39", foreground : "#000" },
  carrot           : { title : "Carrot",          background : "#e67e22", foreground : "#ffffff" },
  pumpkin          : { title : "Pumpkin",         background : "#d35400", foreground : "#ffffff" },
  coffee           : { title : "Coffee",          background : "#45362E", foreground : "#ffffff" },
  orange           : { title : "Orange",          background : "#FF9800", foreground : "#000" },
  "deep-orange"    : { title : "Deep Orange",     background : "#FF5722", foreground : "#ffffff" },
  brown            : { title : "Brown",           background : "#795548", foreground : "#ffffff" },
  black            : { title : "Black",           background : "#000",    foreground : "#ffffff" },
  white            : { title : "White",           background : "#ffffff", foreground : "#000" },
  'vimeo'          : { title : "Vimeo",           background : "#1AB7EA", foreground : "#ffffff" },
  'youtube'        : { title : "Youtube",         background : "#DC4A38", foreground : "#ffffff" },
  'facebook'       : { title : "Facebook",        background : "#3B5998", foreground : "#ffffff" },
  'twitter'        : { title : "Twitter",         background : "#28A9E0", foreground : "#ffffff" },
  'instagram'      : { title : "Instagram",       background : "#E1306C", foreground : "#ffffff" },
  'linkedin'       : { title : "Linkedin",        background : "#0072b1", foreground : "#ffffff" },
  'whatsapp'       : { title : "Whatsapp",        background : "#075E54", foreground : "#ffffff" }
}