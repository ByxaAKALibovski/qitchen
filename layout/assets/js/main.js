function preloadImage(input){
    if(input.files && input.files[0]){
        let label = input.parentNode;
        let reader = new FileReader();
        reader.onload = function (e){
            if(label.querySelector('p') != null) label.querySelector('p').remove();
            if(label.querySelector('img') != null) label.querySelector('img').remove();
            label.innerHTML += `<img src="${e.target.result}" style="width: auto; height: 50px;">`;
        }
        reader.readAsDataURL(input.files[0]);
    }else{
        let label = input.parentNode;
        if(label.querySelector('img') != null) label.querySelector('img').remove();
        label.innerHTML += `<p>Прикрепите изображение</p>`;
    }
}
function delBasketItem(el){
    var basketItem = el.parentNode.parentNode.parentNode.nodeName == "LI" ? el.parentNode.parentNode.parentNode : '';
    basketItem = el.parentNode.parentNode.parentNode.parentNode.nodeName == "LI" ? el.parentNode.parentNode.parentNode.parentNode : basketItem;
    basketItem = el.parentNode.parentNode.parentNode.parentNode.parentNode.nodeName == "LI" ? el.parentNode.parentNode.parentNode.parentNode.parentNode : basketItem;

    basketItem.remove();
}
function counterBlock(el){
    var basketItem = el.parentNode.parentNode.parentNode.parentNode,
        basketItemId = basketItem.getAttribute('data-basket-item'),
        increment = el.classList.contains('incremet') ? true : false,
        counterInput = el.parentNode.querySelector('input');

    if(increment){
        counterInput.value = Number(counterInput.value) + 1;
        changeCountBasketItem(counterInput.value, basketItemId)
        return;
    }

    if(Number(counterInput.value) == 1) return;
    
    counterInput.value = Number(counterInput.value) - 1;
    changeCountBasketItem(counterInput.value, basketItemId)
    return;
}
function counterInput(el){
    var basketItem = el.parentNode.parentNode.parentNode.parentNode,
        basketItemId = basketItem.getAttribute('data-basket-item'),
        inputCounter = el,
        countItem = Number(inputCounter.value) < 1 ? 1 : Number(inputCounter.value);

    inputCounter.value = countItem;

    changeCountBasketItem(countItem, basketItemId);
    return;
}
function changeCountBasketItem(count, idItem){
    // alert("New count: "+count);
    // alert("Id item basket: "+idItem); 
}

document.addEventListener("change", function(e){
    if(e.target.nodeName == "INPUT" && e.target.getAttribute('type') == "file") preloadImage(e.target);

    if(e.target.classList.contains('count__dish')) counterInput(e.target);
});

document.addEventListener("click", function(e){
    if(e.target.parentNode.classList.contains("menu__nav")){
        document.querySelector('.menu__nav a.active') != null ? document.querySelector('.menu__nav a.active').classList.remove('active'): '';
        e.target.classList.add("active");
    } 

    if(e.target.classList.contains("del__basket_item") || e.target.parentNode.classList.contains("del__basket_item") || e.target.parentNode.parentNode.classList.contains("del__basket_item")) delBasketItem(e.target);
    if(e.target.classList.contains("incremet") || e.target.classList.contains("dicrement")) counterBlock(e.target);

})

document.addEventListener('DOMContentLoaded', () => {
    window.location.hash ? document.querySelector(`.menu__nav a[href="menu.html${window.location.hash}"]`).classList.add('active'): '';
});

document.addEventListener('DOMContentLoaded', () => {
  const content = document.querySelector('.content');
  const nav = document.querySelector('.menu__nav');

  // Функция для проверки положения прокрутки и управления классом scroll
  const checkScrollPosition = () => {
    if (content.scrollTop > 0) {
      nav.classList.add('scroll');
    } else {
      nav.classList.remove('scroll');
    }
  };

  // Проверка при прокрутке контейнера .content
  content.addEventListener('scroll', checkScrollPosition);

  // Обработчик для переходов по якорям
  window.addEventListener('hashchange', () => {
    // Задержка, чтобы учесть завершение плавной прокрутки
    setTimeout(checkScrollPosition, 100);
  });

  // Проверка при загрузке страницы (для случаев, когда страница загружается с якорем)
  checkScrollPosition();
});

document.addEventListener('submit', function(e){
    if(e.target.getAttribute('action') == "add_reservation"){
        e.preventDefault();

        e.target.style.display = "none";
        document.querySelector('.success__block').style.display = "flex";
    }
});