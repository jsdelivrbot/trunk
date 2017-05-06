const fileSystem = require('fs');
const path = require('path');
const electron = require('electron');
const remote = electron.remote;
const ipc = electron.ipcRenderer;
const dialog = remote.dialog;

window.editor = null;
window.activeFile = null;
window.savedContent = null;
window.titlePrefix = document.title + " - ";

(function () {
    window.editor = new SimpleMDE({
        element: document.getElementById('editor'),
        forceSync: true,
        spellChecker: false,
        autofocus: true,
        autoDownloadFontAwesome: false,
        toolbar: [
            {
                name: "new",
                action: function () {
                    return loadFile();
                },
                className: "fa fa-file-o",
                title: "New file"
            }, {
                name: "open",
                action: function () {
                    return openFile();
                },
                className: "fa fa-folder-open-o",
                title: "Open file"
            }, {
                name: "save",
                action: function () {
                    return saveFile(false);
                },
                className: "fa fa-floppy-o",
                title: "Save file"
            }, {
                name: "save-as",
                action: function () {
                    return saveFile(true);
                },
                className: "fa fa-files-o",
                title: "Save file as"
            }, "|",
            "undo", "redo", "|",
            "bold", "italic", "heading", "|",
            "code", "quote", "unordered-list", "ordered-list", "|",
            "link", "image", "table", "|",
            "preview", "side-by-side", "|",
            "guide"
        ]
    });

    var setOption = window.editor.codemirror.setOption, fullscreen = function (lazy) {
        window.editor.codemirror.setOption('fullScreen', true);
        document.getElementsByTagName("html")[0].style = "overflow: hidden;";
        document.getElementsByClassName('editor-toolbar')[0].className = 'editor-toolbar fullscreen';
        if (lazy) {
            window.setTimeout(fullscreen, 0);
            window.setTimeout(fullscreen, 1);
            window.setTimeout(fullscreen, 10);
            window.setTimeout(fullscreen, 100);
        }
    };
    window.editor.codemirror.setOption = function(option, value) {
        if ('fullScreen' === option && false === value) {
            fullscreen(true);
        } else {
            setOption.apply(this, arguments);
        }
    };
    fullscreen();
    window.savedContent = window.editor.value();

    loadFile();
})();

ipc.on('closingWindow', function() {
    if (beforeDestroy()) {
        ipc.send('doClose');
    }
});

function beforeDestroy() {
    if (window.editor.value() === window.savedContent) {
        return true; // nothing to destroy
    }

    var choice = dialog.showMessageBox(remote.getCurrentWindow(), {
        type: 'warning',
        buttons: ['Cancel', 'Don\'t Save'],
        title: 'Destroy changes?',
        message: 'You have unsaved changes. Would you like to don\'t save them?',
        detail: 'Your changes will be lost if you don\'t save them.'
    });

    switch (choice) {
        case 1:
            window.editor.value(window.savedContent);
            return true;
        default:
            return false;
    }
}

function loadFile(file) {
    if (beforeDestroy()) {
        if (file) {
            fileSystem.readFile(file, 'utf-8', function (err, data) {
                window.editor.value(data);
                window.savedContent = window.editor.value();
                window.activeFile = file;
                document.title = window.titlePrefix + file;
            });
        } else {
            window.editor.value("");
            window.savedContent = window.editor.value();
            window.activeFile = file;
            document.title = window.titlePrefix + "New file";
        }
    }
}

function openFile() {
    dialog.showOpenDialog({
        filters: [
            {
                name: 'Markdown',
                extensions: ['md']
            }
        ]
    }, function (fileNames) {
        if (fileNames === undefined) {
            return;
        }
        loadFile(fileNames[0]);
    });
}

function saveFile(saveAs) {
    var save = function (file, content) {
        fileSystem.writeFile(file, content, 'utf-8', function (err) {
            if (!err) {
                window.savedContent = content;
                document.title = window.titlePrefix + file;
            }
        });
    };
    if (saveAs || !window.activeFile) {
        dialog.showSaveDialog({
            filters: [
                {
                    name: 'Markdown',
                    extensions: ['md']
                }
            ]
        }, function (fileName) {
            save(fileName, window.editor.value());
        });
    } else {
        save(window.activeFile, window.editor.value());
    }
}
