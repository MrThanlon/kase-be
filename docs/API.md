# Kase接口文档

## 状态码

|  值  |         含义         |               备注               |
| :--: | :------------------: | :------------------------------: |
|  0   |         正常         |                                  |
|  -1  |       登录失败       |         检查手机号和密码         |
| -10  |      token失效       |           建议重新登录           |
| -12  |      验证码失效      |           建议重新获取           |
| -20  |     短信功能失败     | 注意即使短信失败依然可能执行动作 |
| -30  | 手机号不存在/未注册  |               注册               |
| -40  |     不能重复注册     |             重设密码             |
| -41  |    用户名u不存在     |            检查用户名            |
| -50  |      文件不规范      |             重新上传             |
| -60  |      数据库错误      |            联系开发者            |
| -100 |       请求错误       |            没有按规范            |
| -101 |      项目不存在      |             检查pid              |
| -102 |      名称不规范      |       包含HTML或者PHP标签        |
| -103 |      cid不存在       |             检查cid              |
| -104 |     磁盘空间不足     |                                  |
| -105 | 存储目录没有写入权限 |                                  |
| -106 |     无法保存文件     |             原因未知             |
| -107 |       目录错误       |    配置文件中的FILE_DIR不正确    |
| -108 |     PDF文件错误      |        不是正常的PDF文件         |
| -109 |     ZIP文件错误      |        不是正常的ZIP文件         |
| -110 |     无法读取文件     |                                  |
| -111 |      材料已审核      |           不能重复审核           |
| -112 |      gid不存在       |             检查gid              |
| -113 |     分值超过设定     |                                  |
| -114 |      qid不存在       |             检查qid              |
| -115 |   表格文件无法解析   |      不要修改表格的其他部分      |
| -200 |     后端未知错误     |            联系开发者            |

## 术语

- *课题（content/subject）*：由申报人提交
- *项目（project）*：一个项目下包含多个项目分区，二级管理员控制
- *材料分组（project group）*：对项目的细分，用于对评审人员和课题分组，二级管理员控制
- *用户（user）*，用户有四类：
  1. *申报人（applicant）*：申报人可以在页面注册、重设密码、提交材料、绑定邮箱，审核状态改变时通过短信和邮箱通知申报人。注册只能使用手机号。
  2. *评审员（judge）*：评审员账户和密码都由二级管理员分配，给通过审核的课题打分。
  3. *二级管理员（administrator）*：由超级管理员分配账户，管理评审员。
  4. *超级管理员（system）*：系统操作者，管理二级管理员。

## 关于token

token会放到响应的cookie中，键名为`token`。token为经过base64编码的信息，尾部为HMAC签名，结构如下：

```json
{
    u: String, //用户名，申报人的是手机号，其他的是自定义字符串
    uid: Number, //用户唯一号码
    type: Number, //用户身份，0.未启用，1.申报人，2.审核员，3.管理员
    version: Number, //用于实现登出和修改密码后token失效的功能
    expire: Number, //过期时间，unix时间戳
    born: Number //生成token的时间，unix时间戳
}
```



## 关于接口

如果没有说明，默认返回JSON。

如果没有说明，请求参数使用kv对。

## 关于短信

短信功能使用腾讯云SMS平台，具体配置见`config.php`。

暂时不适配其他平台的接口，如有需要可以修改`sms.php`。

短信功能包括验证码和通知，分别对应函数

## 用户接口

### 使用密码登录

@request

```json
{
    URL: "user/login",
    method: "POST",
    param: {
        u: String, //申请人的是手机号，其他人是字符串
        p: String //密码
    }
}
```

@return

```json
{
	status: Number,
	msg: String
}
```

### 请求登录验证码

不需要cookie。

@request

```json
{
    URL: "user/sms",
    method: "POST",
    param: {
        u: Number //手机号
    }
}
```

@return

```json
{
	status: Number,
	msg: String
}
```

### 验证码/无密码登录

用于注册账号和修改密码，此一次性token在请求后失效，请求成功后token会放置到响应的cookie中。相当于无密码登录。

@request

```json
{
    URL: "user/one_time_login",
    method: "POST",
    param: {
      u: Number, //手机号
    	token: String
    }
}
```

@return

```json
{
	status_code: Number,
	msg: String
}
```

### 用户身份

这个其实不是很有必要，直接解析cookie里面的内容也可以的，理论上仅用于跨域的情况。

@request

```json
{
    URL: "user/id",
    method: "POST"
}
```

@return

```json
{
    status_code: Number,
    msg: String,
    type: Number, //0->未确定，1->申报人，2->审核员，3->二级管理员，4->超级管理员
    uid: Number, //用户编号
    tel: Number, //用户手机号，没有则为0
  	username: String //用户名
}
```

### 重设密码

需要cookie。

@request

```json
{
    URL: "user/reset_password",
    method: "POST",
    param: {
      	op: String, //原密码
        p: String //新密码
    }
}
```

@return

```json
{
    status_code: Number,
    msg: String
}
```

### 请求注册账户

如果手机号没有注册，可以请求此接口，系统会发送一条包含注册链接(等于验证码登录)的短信到提交的手机上。如果该手机号已经注册，则不会发送，并且响应为失败。不需要cookie。如果请求注册成功，则会设置cookie，此时账户status为0，需要设置密码才能启用。如果不慎丢失cookie，请使用短信验证码登录。

@request

```json
{
    URL: "user/reg",
    method: "POST",
    param: {
        u: String //手机号
    }
}
```

@return

```json
{
    status_code: Number,
    msg: String
}
```

### 登出

@request

```json
{
    URL: "user/logout",
    method: "POST"
}
```

@return

```json
{
    status_code: Number,
    msg: String
}
```

## 数据接口

### 申报人

#### 拉取课题列表

@request

```json
{
    URL: "data/app/list",
    method: "POST"
}
```

@return

```json
{
    status_code: Number,
    msg: String,
    data: [
        {
        	name: String, //课题名称
        	cid: Number, //课题唯一编号
        	pid: Number, //隶属于的项目的编号
        	applicant: String, //申请人
        	tel: String, //申请人手机号
        	status: Number, //材料状态，0->未审核，1->已过审，-1->审核未通过
          pid: Number, //所属项目
          time: String, //日期,YYYY-MM-DD HH:mm:SS
          pdf: Boolean, //是否有文档
          zip: Boolean, //是否有附件
          score: Number //分数，没有则为null
        },
        ...
    ]
}
```

#### 拉取单个课题

@request

```json
{
    URL: "data/app/subject",
    method: "POST",
  	param: {
      	cid: Number // 课题ID
    }
}
```

@return

```json
{
    status_code: Number,
    msg: String,
    data: {
        	name: String, //课题名称
        	cid: Number, //课题唯一编号
        	pid: Number, //隶属于的项目的编号
        	applicant: String, //申请人
        	tel: String, //申请人手机号
        	status: Number, //材料状态，0->未审核，1->已过审，-1->审核未通过
          pid: Number, //所属项目
          time: String, //日期,YYYY-MM-DD HH:mm:SS
          pdf: Boolean, //是否有文档
          zip: Boolean, //是否有附件
          score: Number //分数，没有则为null
    }
}
```



#### 查询课题分数

@request

```json
{
    URL: "data/app/query_score",
    method: "POST",
  	param: {
      cid: Number //课题编号
    }
}
```

@return

```json
{
  	status_code: Number,
    msg: String,
  	data: {
      total: Number, //总分
      detail: [
        {
          text: String, //题目
          score: Number //分数
        },
        ...
      ]
    }
}
```

#### 拉取项目列表

@request

```json
{
    URL: "data/app/list_prj",
    method: "POST"
}
```

@return

```json
{
    status_code: Number,
    msg: String,
    data: [
        {
            name: String, //项目名称
            pid: Number //项目编号
        },
        ...
    ]
}
```

#### 拉取通知信息

放在申报页的内容。

@request

```json
{
    URL: "data/app/notice",
    method: "POST"
}
```

@return

```json
{
    status_code: Number,
    msg: String,
    content: String //内容
}
```

#### 创建新申请

@request

```json
{
    URL: "data/app/new_app",
    method: "POST",
    param: {
        name: String, //课题名称
        pid: Number, //隶属于的项目的编号
        applicant: String //申请人
    }
}
```

@return

```json
{
    status_code: Number,
    msg: String,
    cid: Number //申请材料的编号
}
```

#### 上传PDF

只接受PDF文件，后缀名为`pdf`的文件，会验证文件是否确实为PDF格式。重复上传则替换原来的文件。使用form-data。已经上传过的不允许再次上传。

@request

```json
{
    URL: "data/app/upload_pdf",
    method: "POST",
    param: {
        cid: Number, //申请材料的编号
    		pdf: File //文件
    }
}
```

@return

```json
{
    status_code: Number,
    msg: String
}
```

#### 下载PDF

注意，直接就是文件，不是JSON字符串。

@request

```json
{
    URL: "data/app/download_pdf",
    method: "GET",
    param: {
        cid: Number //申请材料的编号
    }
}
```

@return PDF File

#### 上传附件

只接收后缀名为`zip`的压缩文件，会尝试解压文件。重复上传则替换原来的文件。已经上传过的不允许再次上传。

@request

```json
{
    URL: "data/app/upload_zip",
    method: "POST",
    param: {
        cid: Number, //申请材料的编号
    		zip: File //文件
    }
}
```

#### 下载附件

直接就是文件，不是json。

@request

```json
{
    URL: "data/app/download_zip",
    method: "GET",
    param: {
        cid: Number //申请材料的编号
    }
}
```

@return ZIP File

### 审核员

#### 拉取通知信息

@request

```json
{
    URL: "data/jug/notice",
    method: "POST"
}
```

@return

```json
{
    status_code: Number,
    msg: String,
    content: String //内容
}
```

#### 拉取课题列表

所有课题。

@request

```json
{
    URL: "data/jug/list",
    method: "POST"
}
```

@return

```json
{
    status_code: Number,
    msg: String,
    data: [
        {
        	name: String, //课题名称
        	cid: Number, //课题唯一编号
        	applicant: String, //申请人
          status: Number, //状态
          zip: Boolean, //是否上传附件
          time: String //提交时间
        },
        ...
    ]
}
```

####　评分

@request

```json
{
    URL: "data/jug/score",
    method: "POST",
    param: {
    	cid: Number, //申请材料的编号
        score: String, //分数，0-100
        pqid: Number //题目，对应表格，0表示仅总分
    }
}
```

@return

```json
{
    status_code: Number,
    msg: String
}
```

#### 下载PDF

如果正常，直接下载文件，响应值不是JSON格式而是二进制文件流。

@request

```json
{
    URL: "data/jug/download_pdf",
    method: "GET",
    param: {
        cid: Number //申请材料的编号
    }
}
```

#### 下载附件

如果正常，直接下载文件，响应值不是JSON格式而是二进制文件流。

@request

```json
{
    URL: "data/jug/download_zip",
    method: "GET",
    param: {
        cid: Number //申请材料的编号
    }
}
```

#### 下载打分表

如果正常，直接下载文件，响应值不是JSON格式而是二进制文件流。下载的打分表包括已经打分的材料。打分表中包含文档和附件的超链接。

@request

```json
{
    URL: "data/jug/download_table",
    method: "GET",
    param: {
        pid: Number //项目id
    }
}
```

#### 上传打分表

上传打分表后，后端根据打分表解析然后完成打分，会覆盖之前的分数。如果无法解析则返回失败。使用form-data。支持xls,xlsx,csv文件。

@request

```json
{
    URL: "data/jug/upload_table",
    method: "POST",
    param: {
        pid: Number, //项目id
        file: File //打分表文件
    }
}
```

@return

```json
{
    status_code: Number,
    msg: String
}
```

#### 查询分数

单个评审材料的分数

@request

```json
{
    URL: "data/jug/query_score",
    method: "POST",
    param: {
        cid: Number, //材料的编号
        pqid: Number //project下的子question id
    }
}
```

@return

```json
{
    status_code: Number,
    msg: String,
    score: Number //分数
}
```

### 管理员

#### 拉取项目列表

@request

```json
{
    URL: "data/adm/list",
    method: "POST"
}
```

@return

```json
{
    status_code: Number,
    msg: String,
    data: [
        {
            name: String, //项目名称
            pid: Number, //项目编号
            groups: Number, //分区数量
            contents: Number //材料数量
        },
        ...
    ]
}
```

#### 创建项目

@requests

```json
{
    URL: "data/adm/new_prj",
    method: "POST",
    param: {
        name: String, //项目名称
      	start: Number, //开始时间，unix时间戳
      	end: Number //结束时间，unix时间戳
    }
}
```

#### 拉取项目信息

@request

```json
{
    URL: "data/adm/query_prj",
    method: "POST",
    param: {
        pid: Number
    }
}
```

@respnse

```json
{
    status_code: Number,
    msg: String,
  	data: {
        name: String, //项目名称
        start: Number, //开始时间，unix时间戳
      	end: Number //结束时间，unix时间戳
    }
}
```

#### 修改项目信息

@request

```json
{
    URL: "data/adm/mod_prj",
    method: "POST",
    param: {
        pid: Number, //项目id
        name: String, //项目名称
        start: Number, //开始时间，unix时间戳
      	end: Number //结束时间，unix时间戳
    }
}
```

#### 拉取课题分区

@requests

```json
{
    URL: "data/adm/list_groups",
    method: "POST",
    param: {
        pid: Number //项目编号
    }
}
```

@return

```json
{
    status_code: Number,
    msg: String,
    data: [
        {
        	gid: Number, //分区编号
          eva: [
            	"jug1", //用户名
            	"jug2",
            	...
          ],
          content: [
            	Number, //content ID, cid
            	Number,
            	...
          ]
        },
        ...
    ]
}
```

#### 查询课题分区详细

包括该分区下的材料和评审员有哪些。

@request

```json
{
    URL: "data/adm/query_group",
    method: "POST",
    param: {
        gid: Number //分区编号
    }
}
```

@return

```json
{
    status_code: Number,
    msg: String,
    data: {
        eva: [
            Number, //评审员uid
            ...
        ],
        content: [
            Number, //材料cid
            ...
        ]
    }
}
```

#### 创建课题分区

@request

```json
{
    URL: "data/adm/new_group",
    method: "POST",
    param: {
        pid: Number //项目id
    }
}
```

@return

```json
{
    status_code: Number,
    msg: String,
    gid: Number
}
```

#### 删除课题分区

@request

```json
{
    URL: "data/adm/del_group",
    method: "POST",
    param: {
        gid: Number //分区id
    }
}
```

#### 创建评审员账号

@request

```json
{
    URL: "data/adm/add_user",
    method: "POST",
    param: {
        u: String, //用户名，不能是11位数字，不能使用mysql关键字或者含有尖括号，不能超过20个字符
        p: String //密码
    }
}
```

#### 删除评审员账号

@request

```json
{
    URL: "data/adm/del_user",
    method: "POST",
    param: {
        u: String, //用户名
    }
}
```

#### 拉取项目下的课题列表

返回项目下所有课题信息。

@request

```json
{
    URL: "data/adm/query_content",
    method: "POST",
    param: {
        pid: Number //项目编号
    }
}
```

@return

```json
{
    status_code: Number,
    msg: String,
    data: [
        {
            name: String, //项目名称
            cid: Number, //材料编号
            applicant: String, //申请人
            uid: String, //申请人手机号
            status: Number //材料状态，0->未审核，1->已过审，-1->审核未通过
        },
        ...
    ]
}
```

#### 审核

@request

```json
{
    URL: "data/adm/review",
    method: "POST",
    param: {
        cid: Number, //材料编号
        result: Number //是否通过，1->通过，2->不通过
    }
}
```

@return

```json
{
    status_code: Number,
    msg: String
}
```

#### 分配课题到分区

@request

```json
{
    URL: "data/adm/mod_content",
    method: "POST",
    param: {
        cid: Number, //材料编号
        gid: Number //材料组编号
    }
}
```

#### 从分区移除课题

```json
{
    URL: "data/adm/mod_content_group",
    method: "POST",
    param: {
        cid: Number, //材料编号
        gid: Number //材料组编号
    }
}
```

#### 分配评审到分区

@request

```json
{
    URL: "data/adm/mod_user",
    method: "POST",
    param: {
        gid: Number, //分区编号
        uid: Number //评审编号
    }
}
```

#### 从分区移除评审

@request

```json
{
    URL: "data/adm/mod_user_group",
    method: "POST",
    param: {
        gid: Number, //分区编号
        uid: Number //评审编号
    }
}
```

#### 拉取申请通知

@request

```json
{
    URL: "data/adm/app_notice",
    method: "POST"
}
```

@return

```json
{
    status_code: Number,
    msg: String,
    content: String //内容
}
```

#### 修改申请通知

@request

```json
{
    URL: "data/adm/mod_app_notice",
    method: "POST",
    param: {
        content: String //修改成的内容
    }
}
```

@return

```json
{
    status_code: Number,
    msg: String
}
```

#### 拉取评审通知

@request

```json
{
    URL: "data/adm/jug_notice",
    method: "POST"
}
```

@return

```json
{
    status_code: Number,
    msg: String,
    content: String //内容
}
```

#### 修改评审通知

@request

```json
{
    URL: "data/adm/mod_jug_notice",
    method: "POST",
    param: {
        content: String //修改成的内容
    }
}
```

#### 上传评审材料

只接收后缀名为`zip`的压缩文件，会尝试解压文件。重复上传则替换原来的文件。已经上传过的不允许再次上传。

```json
{
    URL: "data/adm/upload_jug",
    method: "POST",
    param: {
    		zip: File //文件
    }
}
```

#### 上传申报材料

只接收后缀名为`zip`的压缩文件，会尝试解压文件。重复上传则替换原来的文件。已经上传过的不允许再次上传。

```json
{
    URL: "data/adm/upload_app",
    method: "POST",
    param: {
    		zip: File //文件
    }
}
```

#### 评审员列表

@request

```json
{
  	URL: "data/adm/query_user",
  	method: "POST"
}
```

@return

```json
{
    status_code: Number,
    msg: String,
    data: [
      	{
          	u: String, //用户名
          	stat: Number, //0. 已提交打分表，1.未提交
          	time: String //提交时间
        }
    ]
}
```

#### 添加评分内容

@request

```json
{
    URL: "data/adm/add_question",
    method: "POST",
    param: {
        pid: Number, //项目id
        name: String, //题目名称
        comment: String, //题目注释，可以为空
        max: Number //分值，注意不能超过总分
    }
}
```

@return

```json
{
    status_code: Number,
    msg: String,
    qid: Number, //question id
    pqid: Number //project下的子question id
}
```

#### 删除评分内容

@request

```json
{
    URL: "data/adm/del_question",
    method: "POST",
    param: {
        qid: Number //question id
    }
}
```

@return

```json
{
    status_code: Number,
    msg: String
}
```

#### 查询评分内容

@request

```json
{
    URL: "data/adm/query_question",
    method: "POST",
    param: {
        pid: Number //项目id
    }
}
```

@return

```json
{
    status_code: Number,
    msg: String,
    total: Number, //总分
    total_only: Boolean, //是否允许只打总分，true.是，false.否
    data: [
        {
            qid: Number, //题目id
            pqid: Number, //project下的子question id
            name: String, //题目名称
            comment: String, //题目注释
            max: Number //分值
        },
        ...
    ]
}
```

#### 上传打分表

上传打分表用于设置评分内容，将会覆盖之前的设置。

@request

```json
{
    URL: "data/jug/upload_table",
    method: "POST",
    param: {
        pid: Number, //项目id
        file: File //打分表文件
    }
}
```

@return

```json
{
    status_code: Number,
    msg: String
}
```

#### 下载评审员的打分表

如果正常，直接下载文件，响应值不是JSON格式而是二进制文件流。如果没有则响应为空。

@request

```json
{
    URL: "data/adm/download_table",
    method: "GET",
    param: {
        pid: Number, //项目id
        u: Number //评审员账户名
    }
}
```

#### 批量下载评审员的打分表

如果正常，直接下载文件，响应值不是JSON格式而是二进制文件流。如果没有则响应为空。

@request

```json
{
    URL: "data/adm/download_tables",
    method: "POST/json",
    param: {
        pid: Number, //项目id
      	user: [
          	"jug1",
          	"jug2",
          	...
        ]
    }
}
```

#### 下载空的打分表

如果正常，直接下载文件，响应值不是JSON格式而是二进制文件流。如果没有则响应为空。

@request

```json
{
    URL: "data/adm/download_empty_table",
    method: "GET",
    param: {
        pid: Number //项目id
    }
}
```

#### 创建管理员

@request

```json
{
  	URL: "data/adm/new_adm",
  	method: "POST",
  	param: {
      	username: String, //用户名
      	password: String //密码
    }
}
```

#### 删除管理员

@request

```json
{
  	URL: "data/adm/del_adm",
  	method: "POST",
  	param: {
      	username: String //用户名
    }
}
```

#### 拉取管理员列表

@request

```json
{
  	URL: "data/adm/list_adm",
  	method: "POST"
}
```

@return

```json
{
    status_code: Number,
    msg: String,
  	data: [
      	"adm1",
      	"adm2",
      	...
    ]
}
```



