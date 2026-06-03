(()=>{
    const sendBtn=document.getElementById('sendCodeBtn'),sendTimerText=document.getElementById('sendTimerText'),sendCountdown=document.getElementById('sendCountdown'),SEND_DELAY=60;
    const TIMER_STORAGE_KEY='takeover_timer_start';
    let remaining=SEND_DELAY;
    let timerInterval=null;
    
    function initTimer(){
        const storedStartTime=localStorage.getItem(TIMER_STORAGE_KEY);
        if(storedStartTime){
            const startTime=parseInt(storedStartTime);
            const currentTime=Date.now();
            const elapsedSeconds=Math.floor((currentTime-startTime)/1000);
            remaining=SEND_DELAY-elapsedSeconds;
            if(remaining<=0){
                remaining=0;
                timerExpired();
            }else{
                startTimer(remaining);
            }
        }else{
            startTimer(remaining);
        }
    }
    
    function startTimer(seconds){
        remaining=seconds;
        if(seconds===SEND_DELAY){
            localStorage.setItem(TIMER_STORAGE_KEY,Date.now().toString());
        }
        updateTimerDisplay();
        if(timerInterval)clearInterval(timerInterval);
        timerInterval=setInterval(function(){
            remaining--;
            if(remaining<=0){
                clearInterval(timerInterval);
                timerExpired();
            }else{
                updateTimerDisplay();
            }
        },1000);
    }
    
    function updateTimerDisplay(){
        const m=Math.floor(remaining/60),s=remaining%60;
        sendCountdown.textContent=`${m}:${String(s).padStart(2,'0')}`;
    }
    
    function timerExpired(){
        sendBtn.disabled=!1;
        sendTimerText.textContent='';
        sendTimerText.classList.remove('active');
        localStorage.removeItem(TIMER_STORAGE_KEY);
    }
    
    initTimer();
    document.getElementById('sendCodeForm').addEventListener('submit',()=>{sendBtn.classList.add('loading');sendBtn.disabled=!0});
    const boxes=Array.from(document.querySelectorAll('.otp-box')),hiddenCode=document.getElementById('otp_code');
    boxes.forEach((box,idx)=>{
        box.addEventListener('input',e=>{
            box.value=box.value.replace(/\D/g,'').slice(-1);
            box.classList.toggle('filled',box.value!=='');
            if(box.value&&idx<boxes.length-1)boxes[idx+1].focus();
            syncHidden();
        });
        box.addEventListener('keydown',e=>{
            if(e.key==='Backspace'&&!box.value&&idx>0){
                boxes[idx-1].value='';
                boxes[idx-1].classList.remove('filled');
                boxes[idx-1].focus();
                syncHidden();
            }
        });
        box.addEventListener('paste',e=>{
            e.preventDefault();
            const pasted=(e.clipboardData||window.clipboardData).getData('text').replace(/\D/g,'').slice(0,6);
            pasted.split('').forEach((ch,i)=>{
                if(boxes[i]){boxes[i].value=ch;boxes[i].classList.add('filled');}
            });
            const nextEmpty=boxes.findIndex(b=>!b.value);
            (nextEmpty>=0?boxes[nextEmpty]:boxes[5]).focus();
            syncHidden();
        });
    });
    function syncHidden(){hiddenCode.value=boxes.map(b=>b.value).join('');}
    const verifyBtn=document.getElementById('verifyCodeBtn'),verifyForm=document.getElementById('verifyCodeForm');
    verifyForm.addEventListener('submit',e=>{
        syncHidden();
        if(hiddenCode.value.length<6){
            e.preventDefault();
            boxes[hiddenCode.value.length]?.focus();
            return;
        }
        verifyBtn.classList.add('loading');
        verifyBtn.disabled=!0;
    });
})();
