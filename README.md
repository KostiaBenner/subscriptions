# Пакет подписок для Laravel 
## Установка
`composer require nikservik/subscriptions`
### Конфигурация
`vendor:publish --provider="Nikservik\Subscriptions\SubscriptionsServiceProvider" --tag=config`
#### Настройка CloudPayments
Включите уведомление Receipt в настройках сервера в личном кабинете CloudPayments
`https://your-server.com/api/cp/receipt`
Путь (Route) `api/cp/receipt` регистрируется автоматически. 
#### Добавление public key в `.env`
Скопируйте ключи доступа из ЛК CloudPayments и сохраните в найтройках окружения `.env`
```
CLOUDPAYMENTS_PUBLICID=
CLOUDPAYMENTS_SECRET=
```
### Миграции
`vendor:publish --provider="Nikservik\Subscriptions\SubscriptionsServiceProvider" --tag=migrations`
`php artisan migrate`
### Модель User
`use Subscription;`

## Список возможностей для тарифов 
В файле `config/subscriptions.php`
```
    'features' => [ // demo set
        'see-welcome',
        'see-sky',
        'read-books',
        'earn-money',
    ],
```

## Локализация
`vendor:publish --provider="Nikservik\Subscriptions\SubscriptionsServiceProvider" --tag=translations`
Копирует файлы локалицации в папку `resources/lang/vendor/subscriptions`
Названия возможностей для тарифов в файле `features.php`
## Уведомления на email
Для стандартных уведомлений нужно короткое название приложения в файле локализации `app.php`. Это название будет вставляться в заголовки писем и в подпись.
Пример `resources/lang/ru/app.php`
```
return [
    'name' => 'Скелетон',
];
```
Пропишите корректный `url` в настройках приложения. Он берется из файла конфигурации `config/app.php`, переменная `url`.  Но удобнее указать в настройках окружения `.env` в переменной `APP_URL`.
Изменить текст уведомлений можно в файлах локализации в папке `resources/lang/vendor/subscriptions/emails` (опубликуйте локализации, чтобы они туда скопировали). 
Если у вас используется несколько языков, то добавьте в `App\User` метод `preferredLocale()`, чтобы емейлы автоматически отправлялись пользователю на его языке. 
### Тарифы
### Код для подписки
### Воркеры для продления подписок
### Проверка доступа

## Использование
Фасад Subscriptions
Авторизация
Тарифы и подписки
