document.addEventListener('DOMContentLoaded',()=>{
 const form=document.getElementById('a3x-quiz-form');if(!form)return;
 const fields=[...form.querySelectorAll('.a3x-question')];if(!fields.length)return;
 const prev=document.getElementById('a3x-prev'),next=document.getElementById('a3x-next'),submit=document.getElementById('a3x-submit');
 const message=document.getElementById('a3x-quiz-error'),counter=document.getElementById('a3x-counter');
 const progress=document.getElementById('a3x-progress-bar'),progressLabel=document.getElementById('a3x-progress-label');
 const dialog=document.getElementById('a3x-confirm');let index=0,confirmed=false;
 const answered=()=>fields.filter(f=>!!f.querySelector('input:checked')).length;
 function render(){fields.forEach((f,i)=>{f.hidden=i!==index});prev.disabled=index===0;next.hidden=index===fields.length-1;submit.hidden=index!==fields.length-1;const n=answered();counter.textContent=`${index+1} dari ${fields.length} · ${n} terjawab`;const pct=Math.round(n/fields.length*100);progress.style.width=pct+'%';progressLabel.textContent=pct+'% selesai';message.hidden=true;}
 function valid(){if(!fields[index].querySelector('input:checked')){message.hidden=false;return false;}return true;}
 form.addEventListener('change',render);next.addEventListener('click',()=>{if(valid()){index++;render();}});prev.addEventListener('click',()=>{index=Math.max(0,index-1);render();});
 form.addEventListener('submit',e=>{if(confirmed)return;e.preventDefault();if(answered()!==fields.length){index=fields.findIndex(f=>!f.querySelector('input:checked'));render();message.hidden=false;return;}if(typeof dialog.showModal==='function')dialog.showModal();else if(window.confirm('Kirim jawaban tes RIASEC?')){confirmed=true;form.requestSubmit();}});
 document.getElementById('a3x-cancel')?.addEventListener('click',()=>dialog.close());document.getElementById('a3x-confirm-submit')?.addEventListener('click',()=>{dialog.close();confirmed=true;submit.disabled=true;form.requestSubmit();});render();
});