# VideoResolve-php
## 获取视频文件基本信息
> *当前仅实现MP4文件 

### 使用
```

use VideoResolve\MP4Resolve;

$m = new MP4Resolve("test.mp4");

var_dump($m->getExt());

```