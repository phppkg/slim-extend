# Document

- [console](console.md)
- [Database](database.md)
- [controller](controller.md)
- [restful-controller](restful.md)
- [Language](language.md)

## haha

### 权限的一点理解

我们通常更易理解和使用的是 使用一个 url path来指定一个权限

- urlpath: 

```
  /post/add /post/*
```

使用url path来指定权限，粒度更细，更易理解 但比较固定，当url变动时不方便更改

- name: 

```
  createPost managePost
```

使用权限名称来控制权限，更灵活方便，但开始时不容易理解

> 推荐结合 name 和 urlpath 来制定和管理权限
