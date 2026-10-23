{{-- ЗАЯВКА «ДОБАВИТЬ ПРЕДПРИЯТИЕ» --}}
<div class="modal" id="addModal" role="dialog" aria-label="Добавить предприятие">
    <div class="m-box">
        <button class="m-close" data-close>✕</button>
        <div id="addFlow">
            <div class="m-title">Добавить предприятие</div>
            <p class="m-sub">Станьте частью главного городского путеводителя.</p>
            <div class="m-steps"><div class="m-step on"></div><div class="m-step"></div><div class="m-step"></div><div class="m-step"></div></div>
            <div class="m-steps-lbl"><b id="stepLbl">Шаг 1 из 4</b><span id="stepName">Основная информация</span></div>

            <div class="m-pane on" data-pane="0">
                <div class="f-row"><label>Название организации *</label><input class="f-in" id="orgName" placeholder="Например: GASTRO BAR"></div>
                <div class="f-2">
                    <div class="f-row"><label>Категория *</label>
                        <select class="f-in" id="orgCat">
                            <option value="">Выберите категорию</option>
                            <option>Ресторан</option><option>Кафе</option><option>Кофейня</option><option>Бар</option>
                            <option>Магазин</option><option>Салон красоты</option><option>SPA</option><option>Фитнес</option>
                            <option>Автосервис</option><option>Медицинский центр</option><option>Отель</option>
                            <option>Развлечения</option><option>Другое</option>
                        </select>
                    </div>
                    <div class="f-row"><label>Район</label>
                        <select class="f-in">
                            @foreach($districts as $d)<option>{{ $d->name }}</option>@endforeach
                        </select>
                    </div>
                </div>
                <div class="f-row"><label>Краткое описание</label><textarea class="f-in" placeholder="Пара предложений о вашем месте"></textarea></div>
                <div class="f-row"><label>Адрес</label><input class="f-in" placeholder="Улица, дом"></div>
                <div class="f-2">
                    <div class="f-row"><label>Телефон</label><input class="f-in" type="tel" placeholder="+7 ___ ___-__-__"></div>
                    <div class="f-row"><label>Email</label><input class="f-in" type="email" placeholder="info@place.ru"></div>
                </div>
                <div class="f-row"><label>Сайт / социальные сети</label><input class="f-in" placeholder="https://…"></div>
            </div>

            <div class="m-pane" data-pane="1">
                <div class="f-2">
                    <div class="f-row"><label>Имя *</label><input class="f-in" id="personName" placeholder="Как к вам обращаться"></div>
                    <div class="f-row"><label>Должность</label><input class="f-in" placeholder="Владелец, директор…"></div>
                </div>
                <div class="f-2">
                    <div class="f-row"><label>Телефон *</label><input class="f-in" type="tel" placeholder="+7 ___ ___-__-__"></div>
                    <div class="f-row"><label>Email</label><input class="f-in" type="email" placeholder="you@company.ru"></div>
                </div>
            </div>

            <div class="m-pane" data-pane="2">
                <div class="f-row"><label>Логотип и фотографии</label>
                    <div class="drop" id="dropZone">📁<br><b>Перетащите фотографии сюда</b><br>или выберите файлы</div>
                    <input type="file" id="fileInput" multiple accept="image/*" hidden>
                    <div class="drop-files" id="fileChips"></div>
                </div>
                <div class="f-row"><label>Instagram / VK / Telegram</label><input class="f-in" placeholder="@yourplace"></div>
                <div class="f-row"><label>Ссылка на сайт</label><input class="f-in" placeholder="https://…"></div>
            </div>

            <div class="m-pane" data-pane="3">
                <label class="chk"><input type="checkbox" class="req-chk"><i>✓</i><span>Я подтверждаю достоверность предоставленной информации</span></label>
                <label class="chk"><input type="checkbox" class="req-chk"><i>✓</i><span>Я согласен на обработку персональных данных</span></label>
                <label class="chk"><input type="checkbox" class="req-chk"><i>✓</i><span>Я принимаю условия размещения информации на платформе «ВИЗИТ ДОНЕЦК»</span></label>
            </div>

            <div class="m-nav">
                <button class="btn btn-ghost" id="mBack" hidden>← Назад</button>
                <button class="btn btn-grad" id="mNext">Далее →</button>
            </div>
        </div>
        <div class="m-success" id="addSuccess" hidden>
            <div class="s-ico"><svg viewBox="0 0 40 40"><path d="M10 21 L17 28 L30 13"/></svg></div>
            <h3>Заявка отправлена</h3>
            <p>Мы получили информацию о вашей организации и свяжемся с вами после проверки.</p>
            <button class="btn btn-ghost" data-close>Отлично</button>
        </div>
    </div>
</div>

{{-- LEAD-МОДАЛКА ПРЕЗЕНТАЦИИ --}}
<div class="modal" id="leadModal" role="dialog" aria-label="Получить презентацию">
    <div class="m-box">
        <button class="m-close" data-close>✕</button>
        <div id="leadFlow">
            <div class="m-title">Получите презентацию «ВИЗИТ ДОНЕЦК»</div>
            <p class="m-sub">Оставьте контактные данные, и мы откроем доступ к презентации проекта.</p>
            <div style="height:20px"></div>
            <div class="f-2">
                <div class="f-row"><label>Имя *</label><input class="f-in" placeholder="Ваше имя"></div>
                <div class="f-row"><label>Компания / организация</label><input class="f-in" placeholder="Название компании"></div>
            </div>
            <div class="f-2">
                <div class="f-row"><label>Должность</label><input class="f-in" placeholder="Ваша должность"></div>
                <div class="f-row"><label>Телефон *</label><input class="f-in" type="tel" placeholder="+7 ___ ___-__-__"></div>
            </div>
            <div class="f-row"><label>Email *</label><input class="f-in" type="email" placeholder="you@company.ru"></div>
            <div class="f-row"><label>Что вас интересует?</label>
                <select class="f-in">
                    <option>Размещение в путеводителе</option><option>Партнерство</option><option>Реклама</option>
                    <option>Добавление предприятия</option><option>Другое</option>
                </select>
            </div>
            <div style="height:8px"></div>
            <label class="chk"><input type="checkbox" class="req-chk"><i>✓</i><span>Я согласен на обработку моих персональных данных</span></label>
            <label class="chk"><input type="checkbox" class="req-chk"><i>✓</i><span>Я принимаю Политику конфиденциальности</span></label>
            <label class="chk"><input type="checkbox"><i>✓</i><span>Я согласен получать информацию о проекте «ВИЗИТ ДОНЕЦК» (необязательно)</span></label>
            <div class="m-nav"><button class="btn btn-grad" id="leadSubmit" style="flex:1">Получить презентацию</button></div>
        </div>
        <div class="m-success" id="leadSuccess" hidden>
            <div class="s-ico"><svg viewBox="0 0 40 40"><path d="M10 21 L17 28 L30 13"/></svg></div>
            <h3>Презентация готова</h3>
            <p>Спасибо за интерес к проекту «ВИЗИТ ДОНЕЦК». Доступ к PDF открыт.</p>
            <div class="m-nav" style="justify-content:center">
                <button class="btn btn-grad" id="dlOnce">Скачать презентацию</button>
                <button class="btn btn-ghost" id="dlAgain">Скачать PDF еще раз</button>
            </div>
        </div>
    </div>
</div>

<div class="toast" id="toast"></div>
