# :llama: AlpacaPHP

![Cover image of AlpacaPHP](https://i.imgur.com/XXuMFlA.jpg)

### :zap: Setup & Using

- Clone project

  ```bash
  # Clone this project first
  git clone https://github.com/alpaca0x0/AlpacaPHP.git AlpacaPHP
  ```

- Edit configs

  ```bash
  # Enter project folder
  cd ./AlpacaPHP/
  # Copy config example files
  cp ./config.example.php config.php
  cp ./configs/db.example.php configs/db.php
  # Edit config files (choose your own editor)
  vim ./config.php
  vim ./configs/db.php
  ```

- Configure http server (for example: `Nginx`)

  ```bash
  # Setting router in web server
  # For example, nginx (your path may be different):
  vim /etc/nginx/conf.d/default.conf
  ```

  In run on `host` case:

  ```nginx
  # Route all traffic to router.php in project root path
  # p.s. The "root" value set as your own path of project root
  #      The same goes for other fields...
  location ^~ /AlpacaPHP/ {
    root /var/www/html/AlpacaPHP;
    include fastcgi_params;
    fastcgi_param SCRIPT_FILENAME $document_root/router.php;
    fastcgi_pass unix:/run/php/php-fpm.sock;
  }
  ```

  or, In run on `docker` case:

  ```nginx
  location ^~ /AlpacaPHP/ {
    proxy_pass http://x.x.x.x;
  }
  ```

  > Don't forget to change the proxy pass IP above.



:grin: Have fun.

---

<!-- ## :cactus: Update -->

<!-- ### :bug: Bugs -->

<!-- ### :wrench: Issues -->

<!-- ### :seedling: Optimization, Beautify -->

<!-- ### :memo: Todo list -->

<!-- --- -->

## :gear: Structures

說明一些關於該專案的架構，僅僅解釋較為主要或可能造成困惑的部份。

| 目錄 | 說明 |
|------|------|
| `api/` | API 端點 |
| `assets/` | 前端資源（JS、CSS、圖片、插件...等） |
| `classes/` | 核心功能 |
| `components/` | 頁面部件（`header`、`footer` 等） |
| `configs/` | 設定檔 |
| `libraries/` | 僅供後端使用的函式庫（有別於 `assets/plugin/`） |
| `pages/` | 站點頁面 |
| `routers/` | Web Routing 規則 |

### :clipboard: Files

- `config.php` 用於存放該站點的核心參數，如設定站點專案根目錄 `ROOT`，或是開關 `DEBUG` 或 `DEV` 模式等。
- `init.php` 用於初始化站點的核心檔案，會自動的引入`config.php`。
- `router.php` 為入口路由(`Entry Router`)，所有流量都將經過這，由該檔案將流量導至其他主路由(`Main Routers`)，且該檔案會自動引入`init.php`。

### :open_file_folder: Folders

- **`api`**\
  一些專門用於獲取資料的頁面。
  用於驗證的頁面，通常採用 Ajax 請求，並回應 Json 格式。通常情況下，回應欄位有以下幾種：
  - `type` 作為回應類別，通常有如下幾種常見的類型：
    - `success` 成功執行
    - `warning` 請求存在問題，而無法完成
    - `error` 伺服端錯誤
    - `info` 單純的顯示訊息
  - `status` 回應狀態碼，同個功能下，必須是唯一值，用於表明請求的處理狀態，且不能含有空格。例如`is_login`或`data_not_found`...等。
  - `data` 用於回傳相關資料，如執行更新請求後，回傳新值等。\
    當然，若是沒有需要回傳的欄位，該欄位可以為`NULL`。
  - `message` 用於顯示的回應訊息，可以含有任意字元，但通常結尾並不會有標點符號。大多數情況下，該欄位被要求必須設定，但在少數情況，該欄位被允許為`NULL`。
  
  當然這並非固定格式，在某些特定功能中，也可能會有其獨有的回應格式。

- **`assets`**\
  前端所會用到的資源，包含常見的 JS、CSS，以及圖片、插件庫等。

- **`classes`**\
  一些核心的功能，以 Class 的方式包裝資料與函式，通常為靜態呼叫。
  - `init` 在該目錄下的檔案會在初始時自動的被載入。

- **`components`**\
  一些常用的頁面部件，如`header`、`footer`等。

- **`configs`**\
  用於存放設定檔的目錄，其`.example`為範例檔案，需要將檔名中的該字節刪除。如`db.example.php`修改內容後更名為`db.php`。

- **`libraries`**\
  與 `assets/plugin` 不同的點在於，`plugin` 資源可於前端調用，而 `libraries` 僅供後端使用。

- **`pages`**\
  存放站點的主要頁面。

- **`routers`**\
  用於存放`Main Router`的目錄。

---

<!-- ## :paw_prints: Router Functions -->

## :mag_right: 路由類型介紹

這可以說是本專案的一大核心功能，簡易且實用的網頁路由。

本專案針對網頁路由設計了一套框架，使後端程式保持簡潔乾淨的同時，讓前端 URI 也更為直覺。

---

### 入口路由 (Entry Router)

首先，如同 HTTP Server 規則設定的那樣，所有流量都會被導向至`/router.php`，以下將其稱作`入口路由 (Entry Router)`，且入口路由只有一個。

接著此入口路由會判斷請求的 URI，並將其導向至設定好的`主路由 (Main Router)`。

---

### 主路由 (Main Router)

指的是那些網站上的頁面分類，常見的分類可能像是 `JS`、`CSS`、`JS`、`API`...等。

也就是一般網址中，撇除掉專案根目錄名稱的第一層分類，例如：

- /`js`/vue.js
- /`css`/tocas.css
- /`api`/account/login/

> 但別誤會，並非所有由網站根目錄後的第一層都需要創立主路由！

> Q：什麼時候需要創建主路由？\
> A：只有當檔案實際位置與 URI 所指路徑不同的時候。

舉例來說，本專案的 `JS`、`CSS` 檔案存放於目錄 `assets/js/`、`assets/css/` 中，但同時又希望前端能夠直接訪問 `js/`、`css/` 就獲取檔案，這時候就必須要設立一個路由規則來對應`js/`、`css/`到`assets/`底下。

---

### 子路由 (Sub Router)

在主路由底下的所有路由統稱 `子路由 (Sub Router)`，且理論上可以無限多層 (不考慮效能的話)。

---

## :information_desk_person: 路由資訊介紹

在開始介紹如何創建及設定你的路由之前，我們必須先對名詞定義有共識。

讓我們先來了解你可以怎麼獲取路由的資訊：

| 名稱 | 大意 |
|---|---|
| `ROOT` | 專案在網址中的根路徑，可於 `/config.php` 中設定。例如 `/AlpacaPHP/`，這表示你的網站由`<domain>/AlpacaPHP/`訪問。若你的網域根目錄即是該專案的入口的話，則請設此參數為 `/`。 |
| `LOCAL` | 專案在伺服器上的本地實體根目錄，用來在路由過程拼接實際檔案路徑。 |
| `Router::uri()` | 目前請求的原始 URI 路徑（不包含 `ROOT`）。 |
| `Router::local()` | 目前路由所對應的後端實體根路徑。 |
| `Router::path()` | 目前路由所接收到的路徑。 |
| `Router::root()` | 路由轉發時累積的前端路徑前綴。 |
| `Router::parmsStr()` | 目前請求的 Query String，例如 `?page=2&sort=asc`。 |

範例：
- 專案在網域上的根目錄 `/MySite/`
- 專案實際位置 `/var/www/AlpacaPHP/`
- 頁面實際位置 `/var/www/AlpacaPHP/account/user/login/`
- 主路由將 `/user/` 開頭的流量導向到專案的 `/account/user/` 中
 
此時訪問 `/MySite/user/login/`，該頁面的路由資訊大概會是這樣：
- `ROOT`：/MySite/
- `Router::uri()`：/user/login/
- `LOCAL`：/var/www/AlpacaPHP/
- `Router::local()`：/account/user/
- `Router::path()`：login/

> :bulb: 你發現了嗎？
> - `ROOT` + `Router::uri()` = 完整前端路徑 (類似於 JS 的 `window.location.pathname`)
> - `LOCAL` + `Router::local()` + `Router::path()` = 該頁面實際檔案的`大致`位置 (真正的位置可能被路由補上其他副檔名)

---

## :hammer: 創建及設定路由

### 創建主路由

你可以在 `/routers/` 底下創建屬於你應用的主路由，其檔名即是該路由的名稱。

創建後，你需要定義該路由的規則。\
起手式如下：

```php
Router::new(...);

Router::view();

http_response_code(404);
```

- `Router::new($local)`\
  這表示該路由的實體根目錄位置，理論上除了入口路由外的路由，都該透過該函式宣告自己的實體根路徑，除非你有需求在實際檔案的同層目錄結構上切換路由(通常不會這麼做)。
  > :warning: 該 `$local` 參數會接續於上層路由的實體根路徑後方。

  > 理論上你仍然可以設定 `Router::new('../parent/')`，但開發上並不建議跨層使用。因為其他層路由通常有它自己的 `Router::new()`，並且是接續它上層路由的實體根路徑下的，這麼跳轉可能會導致路由邏輯混亂。

- `Router::view($filename)`\
  這個函式等同是在告訴系統「不需要再路由了，顯示指定的頁面吧」。\
  其中 `$filename` 就是指定要顯示的頁面真實路徑及檔名，但你可以不指定它，預設將會讓當前路由所獲取的路徑作為輸入。
  > :warning: 該 `$filename` 是由當前路由的實體根路徑後接續的，\
  > 所以理論上你仍然可以`Router::view('../parent/')` 這麼操作，但開發上一樣不建議。

  > :warning: 如果指定的檔案找不到，則路由會嘗試尋找以指定檔名加上後綴 `.php`、`.html`、`/index.php`、`/index.html` 的檔案。\
  > 並且指定檔名會**忽略後方斜線**，所以當你寫`Router::view('file.ext/')`時，路由會尋找在該實體路徑下，依照優先順序判斷是否存在 `file.ext`、`file.ext.php`、`file.ext.html`、`file.ext/index.php`、`file.ext/index.html`，只要檔案存在則會直接路由到該檔案。

- `http_response_code(404)`\
  當一切路由規則無一匹配時，PHP 會接續執行下方的程式，此刻可以自訂自己的 HTTP 回應。\
  你可以像例子中那樣給個 `404` status code，或者自訂一個檔案並再次透過 `Router::view($filename)` 顯示。

舉例來說，你創建了檔案 `/routers/admin.php`，然後因為某些原因你希望他被路由到 `/components/` 當中 (雖然這很奇怪)，那麼你可以這麼設定這個檔案：

```php
Router::new('components/');

Router::view();

http_response_code(404);
```

接著若你將所有 `admin/` 開頭 URI 的路徑都導向到該路由，且存在檔案 `components/info.php`，則你可以透過訪問 `/admin/info/` 來到達該檔案頁面。

若你直接訪問 `/admin/`，則路由也會嘗試尋找 `components/` 底下是否存在 `index.php`、`index.html`。

---

### 創建子路由

子路由的創建大致與主路由一致，差別在於存放的位置。

子路由通常存放在`/routers/`下名為上層路由的名稱的目錄下。

例如主路由為`member`，其下層路由為`user`，而`user`還有下層路由名為`login`，則子路由`login`的實際檔案應在`/routers/member/user/login.php`。

---

### 設定路由規則

可以透過 `Router::get($uris, $callback, $path)` 函式來定義導向到下層路由的規則。

| 參數 | 型別 | 必填 | 說明 |
|---|---|---|---|
| `$uris` | `string \| string[]` | 是 | 要匹配的 URI 前綴，可傳單一路徑（如 `api/`）或多路徑陣列（如 `['js/', 'css/']`）。 |
| `$callback` | `callable \| string` | 是 | 匹配成功後執行。`string` 代表導向到對應路由檔（如 `'api'` 對應 `routers/api.php`）；`callable` 代表直接執行函式。 |
| `$path` | `string \| true` | 否 | 覆寫傳遞給下一層路由的處理路徑。預設時會自動用目前路徑扣除 `$uris` 前綴後的路徑；若為 `true` 則會將當前路由的路徑原封不動轉傳至下層；若為字串，則表示自訂路徑，可以在字串中使用變數`{root}`表示匹配的 $uris；`{path}`表示扣除 $uris 前綴的路徑。 |

> 喔對了，請別誤會，此處的 `get` 與 HTTP Method 的 `GET` 是**沒有關聯**的，該方法只是剛好也叫這個名稱，本專案之路由功能並**不能**也**不需要**區分請求類型。

> 本專案創建之初的理念認為，請求方法的判斷應於網頁上進行，路由的工作僅僅是導向到正確檔案及解析參數。

`$uris` 在匹配後，若導向到下層路由，則下層路由預設將會收到扣除**匹配的路徑**的 URI。

舉例來說，一般情況下路由規則設定如下：
> 
> ```php
> Router::get('forest/', 'nature')
> ```
> 
> 接著訪問`/forest/tiger/`，則路由 `nature` 實際收到的路徑為 `tiger/`。

如果你希望路由 `nature` 原封不動地收到上層路徑，則可以直接指定`true`作為路徑直接轉發：
> 
> 
> ```php
> Router::get('forest/', 'nature', true)
> ```
> 
> 這麼一來，訪問`/forest/tiger/` 時，路由 `nature` 實際收到的路徑就會是 `forest/tiger/`。


但如果你希望路由 `nature` 收到的路徑是自訂的樣子，例如 `forest/animal/tiger/`，則你可能需要這麼寫：

> :bulb: Tips: 使用字串自訂要傳遞的路徑，並善用變數 `{root}` 及 `{path}`
> 
> ```php
> Router::get('forest/', 'nature', "{root}animal/{path}")
> ```
>
> `{root}` 被替換成 `forest/`\
> `{path}` 被替換成 `tiger/`

:bulb: `Router::get()` 的優先序是由上至下的

```php
Router::get('user/', 'user');
Router::get('user/api/', 'api'); # 永遠不會被觸發
```

現在，你應該能看懂入口路由在做什麼了：

```php
# /router.php
Router::get(['img/', 'js/', 'css/', 'plugin/'], 'asset', true);
Router::get('api/', 'api');

Router::get('/', 'page');

http_response_code(404);
```

如果你有創建主路由的話，現在就可以在入口路由這邊嘗試導向到主路由囉！

---

## :rocket: 進階規則設定及實用技巧

先前只是介紹了路由的基本用法，以下則會更完整的介紹這套框架所能做的更多事情。

---

### 前端站內導向

`Router::redirect($path, $withPost, $withGet)`

此方法用於前端將使用者導向另一個路徑，並可選擇是否保留 POST 與 GET 參數。

- `$path`：要跳轉的目標路徑（相對 ROOT 之後的路徑）。
- `$withPost`：是否保留 POST 資料（預設 true，會用 307 Temporary Redirect）。
- `$withGet`：是否保留 GET 參數（預設 true）。

常見用途：  
用於一般網頁跳轉、權限驗證失敗時跳轉、或自動修正路徑...等

範例：
```php
if(!$user->isLogin()){
  Router::redirect('/login/');
}
```

---

### 前端任意導向

`Router::jump($url, $withPost, $withGet)`

與 `Router::redirect()` 用法一致，差別在於 `$url` 參數為完整網址。

同樣支援選擇保留 GET、POST 資料的功能。

```php
Router::jump('https://example.com');
```

---

#### 嚴格路徑位置匹配

`Router::equal($uris, $callback, $path)`

完全匹配路由，常見的適用場景是將入口位置導向到指定頁面，例如：

```php
Router::equal('/', function () {
  Router::redirect('/index/');
});
```

有別於 `Router::get()`，上面的設定將不會匹配 `/a/`、`/b/`... 等路徑。

---

#### 頁面參數

`Router::args(&...$vars)`

雖然網頁本身就可以透過 GET、POST 等方式傳遞參數，但本框架提供另一種方式。

你可以在想接收參數的頁面**最上方**使用該函式定義接收的參數，例如：

```php
Router::args($action, $id);
```

你就可以像 `/.../edit/123/` 這樣在網址上傳遞參數\
變數 `$action` 為 `edit`\
變數 `$id` 為 `123`

> :warning: 若要使用該函式，必須寫在頁面的`最上方`！

> :warning: 若頁面沒有呼叫該函式，則無法匹配任何帶有參數的請求。


> :warning: 參數可以少給，但不能多給\
> 同樣的例子中，可以匹配`/user/edit/`甚至`/user/`，但不能匹配`/user/edit/123/456/`，若不匹配則會回應`400` status code。

> :bulb: 可以使用 `Router::$args` 來獲取頁面接收到的參數陣列。

---

## :sparkles: Frameworks & Libraries

### :art: CSS

- [`Tocas-UI`](https://tocas-ui.com)
- [`Animate.css`](https://animate.style/)

### :magic_wand: JS

- [`Vue3`](https://vuejs.org) (esm)
- [`Sweetalert2`](https://sweetalert2.github.io/)
- [`Ajax`](https://projects.jga.me/jquery-builder/) (`Jquery-Ajax-Only` from [`Jquery-Builder`](https://projects.jga.me/jquery-builder/))

<!-- ### :link: Library(ies) -->

---

### :coffee: Developer(s)

- [`Alpaca0x0`](https://github.com/alpaca0x0)

---
