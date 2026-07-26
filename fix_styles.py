import re

# App.vue
with open('resources/js/components/App.vue', 'r', encoding='utf-8') as f:
    app_content = f.read()

# AccountsView.vue
with open('resources/js/components/AccountsView.vue', 'r', encoding='utf-8') as f:
    acc_content = f.read()

# Just a quick check to make sure python is working. We will process in the next step.
print(f"App.vue: {len(app_content)} chars")
print(f"AccountsView.vue: {len(acc_content)} chars")
