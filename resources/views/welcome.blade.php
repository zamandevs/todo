<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Laravel</title>

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,600" rel="stylesheet">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css"
          integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/vue"></script>
    <style>
        html, body {
            background-color: #fff;
            color: #636b6f;
            font-family: 'Nunito', sans-serif;
            font-weight: 200;
            height: 100vh;
            margin: 0;
        }

        .full-height {
            height: 100vh;
        }

        .flex-center {
            align-items: center;
            display: flex;
            justify-content: center;
        }

        .position-ref {
            position: relative;
        }

        .top-right {
            position: absolute;
            right: 10px;
            top: 18px;
        }

        .content {
            text-align: center;
        }

        .title {
            font-size: 84px;
        }

        .links > a {
            color: #636b6f;
            padding: 0 25px;
            font-size: 13px;
            font-weight: 600;
            letter-spacing: .1rem;
            text-decoration: none;
            text-transform: uppercase;
        }

        .m-b-md {
            margin-bottom: 30px;
        }
    </style>
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body>
<div class="flex-center position-ref full-height">

    <div class="content" id="todo">
        <div class="title m-b-md">
            todos
        </div>
        <div>
            <form action="" method="POST" @submit.prevent="addTodo">
                <input type="text" class="form-control" placeholder="Whats Need to be done?" name="title"
                       v-model="title" required>
            </form>
        </div>
        <div style="margin-top: 10px">
            <div class="list-group" v-for="todo in filtered?filtered:todos">
                <div v-if="todo.is_complete==false">
                    <input type="checkbox" class="form-check-input pull-left" @click="onCheck(todo)">
                    {{ method_field('PATCH') }}
                </div>
                <div style="margin-left: 30px">
                        <span class="list-group-item list-group-item-action strike" style="text-align: left"
                              contenteditable="true" id="todo" @input="onEdit(todo,$event)" @onchange="getChange()">
                                <del v-if="todo.is_complete==true">@{{todo.title}}</del>
                            <template v-else>@{{todo.title}}</template></span>
                </div>
            </div>
            <div v-if="todos.length>=1">
                <span class="card">@{{todos.filter(todo=>todo.is_complete==false).length}} items left </span>
                <button type="button" class="btn btn-outline-secondary active" @click="all()">All</button>
                <button type="button" class="btn btn-outline-secondary" @click="inComplete()">Active</button>
                <button type="button" class="btn btn-outline-secondary" @click="completed()">Completed</button>
                <button type="button" class="btn btn-outline-secondary" @click="clearCompleted()"
                        v-if="completeList().length>=1">Clear Completed
                </button>
            </div>
        </div>
    </div>
</div>
<script>
    let vue = new Vue({
        el: '#todo',
        data: {
            todos: '',
            title: '',
            filtered: '',
            editing: '',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Content-type': 'application/json'
            }

        },
        created() {
            this.makeAjax('GET', '/todo').then(res => {
                this.todos = JSON.parse(res)['todos']
            })

            document.addEventListener('click', function () {
                let target = document.querySelector('.onEdit')
                if (target) {
                    let title = target.innerText
                    let data = {'title': title, '_method': 'PATCH'}
                    vue.makeAjax('POST', '/todo/' + vue.editing.id, data, vue.headers).then(res => {
                        vue.todos.find(t => t.id == vue.editing.id).title = title
                        vue.editing = ''
                    })
                    target.classList.remove('onEdit')
                }
            })
        },
        methods: {
            addTodo() {
                let data = {'title': this.title}
                this.makeAjax('POST', '/todo', data, this.headers).then(res => {
                    this.todos.push(JSON.parse(res)['todo'])
                })
                this.title = ''
                this.all()
            },

            onCheck(todo) {
                let data = {'is_complete': true, '_method': 'PATCH'}
                this.makeAjax('POST', '/todo/' + todo.id, data, this.headers).then(res => {
                    if (res)
                        this.todos.find(t => t.id == todo.id).is_complete = true
                })
            },
            onEdit(todo, e) {
                e.target.classList.add('onEdit')
                this.editing = todo
            },

            makeAjax(method, url, data = null, headers = null) {
                return new Promise((resolve, reject) => {
                    let xhr = new XMLHttpRequest();
                    xhr.open(method, url)
                    if (headers) {
                        for (let key in headers) {
                            xhr.setRequestHeader(key, headers[key])
                        }
                    }
                    xhr.onload = () => {
                        resolve(xhr.response)
                    }
                    xhr.send(JSON.stringify(data))
                })
            },
            all() {
                this.filtered = this.todos;
            },
            completed() {
                this.filtered = this.completeList()
            },
            inComplete() {
                this.filtered = this.todos.filter(todo => todo.is_complete == false)
            },
            clearCompleted() {
                let ids = this.completeList().map(todo => todo.id)
                this.makeAjax('DELETE', '/todo/' + ids, data = null, this.headers)
                ids.forEach(id => {
                    this.todos.splice(this.todos.findIndex(todo => todo.id == id), 1)
                })
                this.filtered = this.todos
            },
            completeList() {
                return this.todos.filter(todo => todo.is_complete == true)
            }
        }
    })
</script>
</body>
</html>
