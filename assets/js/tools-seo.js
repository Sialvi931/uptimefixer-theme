/* Uptime Fixer 3.7 no-key live website/SEO tools. */
(function(){
  'use strict';
  var qa=function(s,r){return Array.prototype.slice.call((r||document).querySelectorAll(s));};
  var q=function(s,r){return (r||document).querySelector(s);};
  var esc=function(v){return String(v==null?'':v).replace(/[&<>'"]/g,function(c){return {'&':'&amp;','<':'&lt;','>':'&gt;',"'":'&#39;','"':'&quot;'}[c];});};
  function status(root,message,state){var el=q('.ufx-growth-status',root);if(!el)return;el.textContent=message||'';el.className='ufx-growth-status at-help'+(state?' is-'+state:'');}
  function request(data){
    if(window.UFX_REQUEST)return window.UFX_REQUEST('seo_site_check',data);
    data=data||{};data.action='ufx_seo_site_check';data.nonce=window.UFX_API?UFX_API.nonce:'';
    var body=new URLSearchParams();Object.keys(data).forEach(function(key){body.append(key,data[key]);});
    var controller=window.AbortController?new AbortController():null,timer=controller?setTimeout(function(){controller.abort();},55000):null;
    return fetch(window.UFX_API?UFX_API.ajax:'/wp-admin/admin-ajax.php',{method:'POST',credentials:'same-origin',headers:{'Content-Type':'application/x-www-form-urlencoded;charset=UTF-8'},body:body.toString(),signal:controller?controller.signal:undefined})
      .then(function(response){if(timer)clearTimeout(timer);return response.json().catch(function(){throw new Error('The server returned an invalid response.');});},function(error){if(timer)clearTimeout(timer);if(error&&error.name==='AbortError')throw new Error('The live check took too long. Please try again.');throw error;})
      .then(function(json){if(!json.success)throw new Error(json.data&&json.data.message?json.data.message:'The live check failed.');return json.data;});
  }
  function table(rows){
    if(!Array.isArray(rows)||!rows.length)return '';
    var keys=Object.keys(rows[0]||{});if(!keys.length)return '';
    return '<div class="ufx-seo-detail-table"><table><thead><tr>'+keys.map(function(key){return '<th>'+esc(key)+'</th>';}).join('')+'</tr></thead><tbody>'+rows.map(function(row){return '<tr>'+keys.map(function(key){return '<td>'+esc(row[key])+'</td>';}).join('')+'</tr>';}).join('')+'</tbody></table></div>';
  }
  function render(root,data){
    var metrics=Array.isArray(data.results)?data.results:[];
    var html='<div class="ufx-seo-metrics">'+metrics.map(function(item){return '<div class="ufx-seo-metric"><span>'+esc(item.label)+'</span><strong>'+esc(item.value)+'</strong></div>';}).join('')+'</div>';
    html+=table(data.details||[]);
    if(data.notice)html+='<p class="at-help ufx-seo-notice">'+esc(data.notice)+'</p>';
    var output=q('.ufx-seo-suite-output',root);if(output)output.innerHTML=html;
  }
  qa('[data-ufx-seo]').forEach(function(root){
    var slug=root.getAttribute('data-ufx-seo'),button=q('.ufx-seo-suite-run',root),url=q('#'+slug+'-url',root),keyword=q('#'+slug+'-keyword',root);
    if(!button||!url)return;
    button.addEventListener('click',function(){
      var target=String(url.value||'').trim();if(!target){status(root,'Enter a public website or URL.','error');url.focus();return;}
      if(keyword&&!String(keyword.value||'').trim()){status(root,'Enter a keyword or phrase.','error');keyword.focus();return;}
      button.disabled=true;status(root,'Running a protected live check…','working');var output=q('.ufx-seo-suite-output',root);if(output)output.innerHTML='';
      request({mode:slug,url:target,keyword:keyword?String(keyword.value||'').trim():''}).then(function(data){render(root,data);status(root,'Live check completed.','success');}).catch(function(error){status(root,error.message||'The live check failed.','error');}).then(function(){button.disabled=false;});
    });
    [url,keyword].filter(Boolean).forEach(function(field){field.addEventListener('keydown',function(event){if(event.key==='Enter'){event.preventDefault();button.click();}});});
  });
})();
