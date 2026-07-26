import re
import os

with open('resources/js/components/App.vue', 'r', encoding='utf-8') as f:
    app_content = f.read()

replacements = {
    'style="background-color: var(--bg-base); color: var(--text-primary);"': 'class="app-bg"',
    'style="background-color: var(--bg-surface); border-right: 1px solid var(--border);"': 'class="app-sidebar"',
    'style="border-bottom: 1px solid var(--border);"': 'class="sidebar-header"',
    'style="background: linear-gradient(135deg, #1A56DB, #4B8EF8); box-shadow: 0 4px 12px rgba(26,86,219,0.35);"': 'class="app-logo"',
    'style="color: var(--text-primary);"': 'class="text-primary-color"',
    'style="color: var(--text-muted);"': 'class="text-muted-color"',
    'style="background-color: var(--primary);"': 'class="nav-indicator"',
    'style="border-top: 1px solid var(--border);"': 'class="sidebar-footer"',
    'style="background: linear-gradient(135deg, #1A56DB, #4B8EF8);"': 'class="user-avatar"',
    'style="color: var(--text-secondary);"': 'class="text-secondary-color"',
    'onmouseenter="this.style.background=\'var(--bg-surface-2)\';this.style.color=\'var(--text-primary)\'"': '',
    'onmouseleave="this.style.background=\'transparent\';this.style.color=\'var(--text-secondary)\'"': '',
    'onmouseenter="this.style.background=\'var(--danger-light)\';this.style.color=\'var(--danger)\'"': '',
    'onmouseleave="this.style.background=\'transparent\';this.style.color=\'var(--text-secondary)\'"': '',
    'style="background-color: var(--bg-base);"': 'class="app-main"',
    'style="backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px); box-shadow: 0 4px 16px rgba(0,0,0,0.15);"': 'class="toast-item"'
}

for k, v in replacements.items():
    if k == 'style="color: var(--text-primary);"':
        # Need to handle merging class if class attribute exists right before or after
        pass
    app_content = app_content.replace(k, v)

# Specifically fix the merged classes for app_content
app_content = app_content.replace('class="min-h-screen font-sans antialiased transition-colors duration-200" class="app-bg"', 'class="min-h-screen font-sans antialiased transition-colors duration-200 app-bg"')
app_content = app_content.replace('class="min-h-screen font-sans antialiased transition-colors duration-200"\n         class="app-bg"', 'class="min-h-screen font-sans antialiased transition-colors duration-200 app-bg"')
app_content = app_content.replace('class="w-60 flex flex-col flex-shrink-0 transition-colors duration-200"\n               class="app-sidebar"', 'class="w-60 flex flex-col flex-shrink-0 transition-colors duration-200 app-sidebar"')
app_content = app_content.replace('class="p-5 flex items-center gap-3" class="sidebar-header"', 'class="p-5 flex items-center gap-3 sidebar-header"')
app_content = app_content.replace('class="w-9 h-9 rounded-xl flex items-center justify-center shadow-lg flex-shrink-0"\n                 class="app-logo"', 'class="w-9 h-9 rounded-xl flex items-center justify-center shadow-lg flex-shrink-0 app-logo"')
app_content = app_content.replace('class="text-sm font-bold tracking-tight" class="text-primary-color"', 'class="text-sm font-bold tracking-tight text-primary-color"')
app_content = app_content.replace('class="text-xs" class="text-muted-color"', 'class="text-xs text-muted-color"')
app_content = app_content.replace('class="absolute left-3 w-0.5 h-4 rounded-full"\n                    class="nav-indicator"', 'class="absolute left-3 w-0.5 h-4 rounded-full nav-indicator"')
app_content = app_content.replace('class="p-3 space-y-1" class="sidebar-footer"', 'class="p-3 space-y-1 sidebar-footer"')
app_content = app_content.replace('class="flex items-center gap-2.5 px-2 py-2 rounded-xl" class="text-primary-color"', 'class="flex items-center gap-2.5 px-2 py-2 rounded-xl text-primary-color"')
app_content = app_content.replace('class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold text-white flex-shrink-0"\n                   class="user-avatar"', 'class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold text-white flex-shrink-0 user-avatar"')
app_content = app_content.replace('class="text-xs font-semibold truncate" class="text-primary-color"', 'class="text-xs font-semibold truncate text-primary-color"')
app_content = app_content.replace('class="text-xs truncate" class="text-muted-color"', 'class="text-xs truncate text-muted-color"')
app_content = app_content.replace('class="w-7 h-7 rounded-lg flex items-center justify-center transition-all"\n                class="text-secondary-color theme-toggle"', 'class="w-7 h-7 rounded-lg flex items-center justify-center transition-all text-secondary-color theme-toggle"')
app_content = app_content.replace('class="w-7 h-7 rounded-lg flex items-center justify-center transition-all"\n                class="text-secondary-color"\n                \n                ', 'class="w-7 h-7 rounded-lg flex items-center justify-center transition-all text-secondary-color theme-toggle"')
app_content = app_content.replace('class="w-full flex items-center gap-2.5 px-3 py-2 rounded-xl text-xs font-medium transition-all"\n              class="text-secondary-color"\n              \n              ', 'class="w-full flex items-center gap-2.5 px-3 py-2 rounded-xl text-xs font-medium transition-all text-secondary-color logout-btn"')
app_content = app_content.replace('class="flex-1 overflow-y-auto transition-colors duration-200"\n              class="app-main"', 'class="flex-1 overflow-y-auto transition-colors duration-200 app-main"')
app_content = app_content.replace('class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium border"\n          class="toast-item"', 'class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium border toast-item"')


# Add scoped style block at the end
style_block = '''
<style scoped>
.app-bg { background-color: var(--bg-base); color: var(--text-primary); }
.app-sidebar { background-color: var(--bg-surface); border-right: 1px solid var(--border); }
.sidebar-header { border-bottom: 1px solid var(--border); }
.app-logo { background: linear-gradient(135deg, #1A56DB, #4B8EF8); box-shadow: 0 4px 12px rgba(26,86,219,0.35); }
.text-primary-color { color: var(--text-primary); }
.text-muted-color { color: var(--text-muted); }
.text-secondary-color { color: var(--text-secondary); }
.nav-indicator { background-color: var(--primary); }
.sidebar-footer { border-top: 1px solid var(--border); }
.user-avatar { background: linear-gradient(135deg, #1A56DB, #4B8EF8); }
.theme-toggle:hover { background-color: var(--bg-surface-2); color: var(--text-primary); }
.logout-btn:hover { background-color: var(--danger-light); color: var(--danger); }
.app-main { background-color: var(--bg-base); }
.toast-item { backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px); box-shadow: 0 4px 16px rgba(0,0,0,0.15); }
</style>
'''

if "<style scoped>" not in app_content:
    app_content += style_block

# AccountsView.vue fixes
with open('resources/js/components/AccountsView.vue', 'r', encoding='utf-8') as f:
    acc_content = f.read()

# Instead of exhaustive replacements, we will replace specific lines that we grepped earlier
acc_replacements = {
    'style="margin-top: 1.5rem;"': 'class="archived-section-mt"',
    'style="margin-top: 1rem; opacity: 0.75;"': 'class="archived-grid-wrap"',
    'style="background-color: rgba(0,0,0,0.02);"': 'class="archived-card"',
    'style="background-color: transparent; border: 1px solid var(--border-strong); opacity: 0.5;"': 'class="archived-icon"',
    'style="color: var(--text-muted);"': 'class="text-muted-color"',
    'style="color: var(--text-secondary);"': 'class="text-secondary-color"',
    'style="color: var(--primary);"': 'class="text-primary-color"',
    'style="margin-bottom: 0;"': 'class="label-no-mb"'
}

for k, v in acc_replacements.items():
    acc_content = acc_content.replace(k, v)

# Fix double classes in AccountsView
acc_content = acc_content.replace('class="archived-section mt-6" class="archived-section-mt"', 'class="archived-section mt-6 archived-section-mt"')
acc_content = acc_content.replace('class="accounts-grid" class="archived-grid-wrap"', 'class="accounts-grid archived-grid-wrap"')
acc_content = acc_content.replace('class="glass-card account-card group" class="archived-card"', 'class="glass-card account-card group archived-card"')
acc_content = acc_content.replace('class="account-icon" class="archived-icon"', 'class="account-icon archived-icon"')
acc_content = acc_content.replace('class="w-5 h-5" class="text-muted-color"', 'class="w-5 h-5 text-muted-color"')
acc_content = acc_content.replace('class="account-name" class="text-secondary-color"', 'class="account-name text-secondary-color"')
acc_content = acc_content.replace('class="nw-label" class="text-primary-color"', 'class="nw-label text-primary-color"')
acc_content = acc_content.replace('class="label" class="label-no-mb"', 'class="label label-no-mb"')

acc_style_block = '''
<style scoped>
.archived-section-mt { margin-top: 1.5rem; }
.archived-grid-wrap { margin-top: 1rem; opacity: 0.75; }
.archived-card { background-color: rgba(0,0,0,0.02); }
.archived-icon { background-color: transparent; border: 1px solid var(--border-strong); opacity: 0.5; }
.text-muted-color { color: var(--text-muted); }
.text-secondary-color { color: var(--text-secondary); }
.text-primary-color { color: var(--primary); }
.label-no-mb { margin-bottom: 0; }
</style>
'''

if ".archived-section-mt {" not in acc_content:
    if "<style scoped>" in acc_content:
        # Append to existing
        acc_content = acc_content.replace('</style>', acc_style_block.replace('<style scoped>\n', '') + '</style>')
    else:
        acc_content += acc_style_block

with open('resources/js/components/App.vue', 'w', encoding='utf-8') as f:
    f.write(app_content)

with open('resources/js/components/AccountsView.vue', 'w', encoding='utf-8') as f:
    f.write(acc_content)

print("Done refactoring App.vue and AccountsView.vue")
