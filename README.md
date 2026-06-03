# Media to AWS S3 Sync

**Media to AWS S3 Sync** is a powerful and standalone WordPress plugin that allows you to effortlessly sync your media library attachments to Amazon S3. With a simple interface and a master toggle switch, this plugin gives you complete control over media synchronization, offloading media delivery from your server to a highly scalable S3 bucket.

Developed by **[Vallarasu Kanthasamy](https://github.com/vallarasuk)**.

## 🚀 Key Features

*   **Seamless S3 Sync**: Quickly sync your media attachments to your AWS S3 bucket.
*   **Global Enable/Disable Toggle**: Immediate frontend control over the sync status.
*   **Sub-size Syncing**: Automatically syncs all WordPress-generated responsive image sub-sizes.
*   **Dynamic URL Rewriting**: Automatically rewrites frontend image URLs to securely serve them directly from S3.
*   **User-friendly Configuration**: Easy to use settings panel in the WordPress admin area.
*   **Lightweight**: Highly performant with no unnecessary bloat.

## ⚙️ How It Works

Here is a visual representation of the plugin's architecture and synchronization flow:

```mermaid
flowchart TD
    A[WordPress Media Library] -->|Upload or Select Image| B(Media Sync Interface)
    B -->|Click 'Sync to S3'| C{Is Plugin Enabled?}
    C -->|Yes| D[Trigger AJAX Action]
    C -->|No| E[Serve Locally]
    D --> F[S3 Helper Class]
    F -->|Uploads Main File & Sub-sizes| G[(AWS S3 Bucket)]
    G -->|Returns S3 URL| H[Save S3 URL to Post Meta]
    H --> I[Frontend URL Rewriting]
    I -->|Serve Image from S3| J((End User View))
```

## 🛠️ Installation & Setup

1. Upload the `media-to-aws-s3-sync` folder to the `/wp-content/plugins/` directory.
2. Activate the plugin through the **Plugins** menu in WordPress.
3. Navigate to **Settings > Media to S3 Sync**.
4. Enable the synchronization using the master toggle.
5. Enter your AWS Access Key, Secret Key, Region, and Bucket name.
6. Start syncing your media directly from the Media Library!

---

## 🔗 About the Developer & Important Links

I build a variety of web applications, browser extensions, and tools for developers. Check out my work below!

### 🌍 Portfolio & Websites
*   [vallarasuk.com](https://vallarasuk.com)
*   [dev.vallarasuk.com](https://dev.vallarasuk.com)
*   [urpage.in](http://urpage.in)

### 🛠️ Apps & Tools
*   [Live TV](https://livetv.vallarasuk.com/)
*   [ATS Resume Maker](https://atsresumemaker.vallarasuk.com/)
*   [Place Finder](https://placefinder.vallarasuk.com/)
*   [Books](https://books.vallarasuk.com/)
*   [Space](https://space.vallarasuk.com/)
*   [Typer](https://typer.vallarasuk.com/)
*   [Developer Resources](https://vallarasuk.com/resources)

### 💻 VS Code Extensions
*   **Auto Console Log**: [VS Code Marketplace](https://marketplace.visualstudio.com/items?itemName=VallarasuKanthasamy.auto-console-log-by-vallarasu-kanthasamy) | [OpenVSX](https://open-vsx.org/extension/VallarasuKanthasamy/auto-console-log-by-vallarasu-kanthasamy)
*   **Universal Spreadsheet Markdown Editor**: [OpenVSX](https://open-vsx.org/extension/VallarasuKanthasamy/universal-spreadsheet-markdown-editor)
*   [My Publisher Profile](https://marketplace.visualstudio.com/publishers/VallarasuKanthasamy)

### 🧩 Chrome Extensions
*   [Tech Stack Checker](https://chromewebstore.google.com/detail/tech-stack-checker/lhcplmfhkmjobfnndaabeddibhimghgf?hl=en)
*   [Opacity Adjuster](https://chromewebstore.google.com/detail/opacity-adjuster/elgajofcbjicopepiodbabodkajnihog?hl=en)

### 👥 Social & Community
*   **LinkedIn**: [linkedin.vallarasuk.com](https://linkedin.vallarasuk.com)
*   **GitHub**: [github.vallarasuk.com](https://github.vallarasuk.com)
*   **Instagram**: [insta.vallarasuk.com](http://insta.vallarasuk.com/)
*   **WhatsApp Community**: [squad.vallarasuk.com](http://squad.vallarasuk.com/)
*   **WhatsApp Group**: [Join Here](https://chat.whatsapp.com/JzCFT47gI6aE8O6mJA96V0)

### 📚 Open Source
*   [Awesome Developer Resources](https://github.com/vallarasuk/awesome-developer-resources)
