import { Controller } from '@hotwired/stimulus'
import { themeManager } from '@grinway/theme-manager'
import { typeChecker } from "@grinway/type-checker"

/**
 * https://core.telegram.org/bots/webapps#initializing-mini-apps
 */
class TelegramWebApp extends Controller {

    tg = null
    userId = null
    queryId = null
    colorTheme = null
    headerColor = null
    backgroundColor = null
    bottomBarColor = null
    userAgentAppVersion = null
    userAgentManufacturer = null
    userAgentModel = null
    userAgentAndroidVersion = null
    userAgentSdkVersion = null
    userAgentPerformanceClass = null

    /**
     * Stimulus lifecycle callback
     */
    connect() {
        this.tgInit()
        this.parseTelegramUserAgent()
        this.registerEvents()
        this.tgSettings()
        this.setColorTheme()
        this.updateFormData()
        this.tgWebAppReady()
    }

    /**
     * Stimulus lifecycle callback
     */
    disconnect() {
        this.unregisterEvents()
    }

    /**
     * API
     *
     * https://core.telegram.org/bots/webapps#initializing-mini-apps (switchInlineQuery)
     */
    switchInlineQuery(query, chooseChatTypes) {
        if (typeChecker.isNotString(query)) {
            return
        }

        this.tg?.switchInlineQuery(query, chooseChatTypes)
    }

    /**
     * API
     *
     * https://core.telegram.org/bots/webapps#initializing-mini-apps (openLink)
     */
    openLink(url, options) {
        if (typeChecker.isNotString(url)) {
            return
        }

        this.tg?.openLink(url, options)
    }

    /**
     * API
     *
     * https://core.telegram.org/bots/webapps#initializing-mini-apps (downloadFile)
     */
    downloadFile(params, callback) {
        if (typeChecker.isNotObject(params)) {
            return
        }

        this.tg?.downloadFile(params, callback)
    }

    /**
     * API
     *
     * https://core.telegram.org/bots/webapps#initializing-mini-apps (showPopup)
     */
    showPopup(params, callback) {
        if (typeChecker.isNotObject(params)) {
            return
        }

        this.tg?.showPopup(params, callback)
    }

    /**
     * API
     *
     * https://core.telegram.org/bots/webapps#initializing-mini-apps (showAlert)
     */
    showAlert(message, callback) {
        if (typeChecker.isNotString(message)) {
            return
        }

        this.tg?.showAlert(message, callback)
    }

    /**
     * API
     *
     * https://core.telegram.org/bots/webapps#initializing-mini-apps (showConfirm)
     */
    showConfirm(message, callback) {
        if (typeChecker.isNotString(message)) {
            return
        }

        this.tg?.showConfirm(message, callback)
    }

    /**
     * API
     *
     * https://core.telegram.org/bots/webapps#initializing-mini-apps (showScanQrPopup)
     */
    showScanQrPopup(params, callback) {
        if (typeChecker.isNotObject(params)) {
            return
        }

        this.tg?.showScanQrPopup(params, callback)
    }

    /**
     * API
     *
     * Usage:
     *        this.openInvoice({
     *             title: 'Product',
     *             description: 'Product description',
     *             currency: 'RUB',
     *             prices: [ // IMAGINE 1.00 TYPE AS 100
     *                 { label: 'Option 1', amount: '10000' },
     *                 { label: 'Option 2', amount: '100' },
     *             ],
     *         }, (status) => {
     *             if ('paid' === status) {
     *             }
     *         })
     *
     * https://core.telegram.org/bots/webapps#initializing-mini-apps (openInvoice)
     * @param payload https://core.telegram.org/bots/api#createinvoicelink
     * @param callback
     */
    async openInvoice(payload, callback) {
        if (typeChecker.isNotObject(payload) || typeChecker.isNotFunction(callback)) {
            return
        }

        const response = await fetch(Routing.generate('app_invoice_link', [], true), {
            method: 'POST',
            body: JSON.stringify(payload),
            headers: {
                "Content-Type": "application/json",
            },
        })
        const invoiceUrl = await response.text()

        if (String(invoiceUrl) === invoiceUrl) {
            this.tg?.openInvoice(invoiceUrl, callback)
        }
    }

    /**
     * Stimulus target getter
     */
    get form() {
        return this.hasFormTarget ? this.formTarget : null
    }

    /**
     * Stimulus connect process
     */
    tgInit() {
        this.tg = window?.Telegram?.WebApp
    }

    /**
     * Stimulus connect process
     */
    parseTelegramUserAgent() {
        const userAgent = this.userAgentValue
        if (typeChecker.isNotString(userAgent) || false === /Telegram-Android/.test(userAgent)) {
            return
        }

        const matches = /Telegram-Android\/(?<app_version>[0-9.]+)\s?\((?<manufacturer>[a-z]+)\s?(?<model>[a-z \-_0-9]+);\s?Android\s?(?<android_version>[0-9.]+);\s?SDK\s?(?<sdk_version>[0-9.]+);\s?(?<performance_class>\w+)\)/i
            .exec(userAgent)
            .groups
        this.userAgentAppVersion = matches['app_version'] ?? null
        this.userAgentManufacturer = matches['manufacturer'] ?? null
        this.userAgentModel = matches['model'] ?? null
        this.userAgentAndroidVersion = matches['android_version'] ?? null
        this.userAgentSdkVersion = matches['sdk_version'] ?? null
        this.userAgentPerformanceClass = matches['performance_class'] ?? null
    }

    /**
     * Stimulus connect process
     *
     * https://core.telegram.org/bots/webapps
     */
    tgSettings() {
        this.tg?.MainButton?.hide()
        this.tg?.SettingsButton?.hide()
        this.tg?.BackButton?.show()
        this.tg?.SecondaryButton?.hide()
        // this.tg?.HapticFeedback
        // this.tg?.CloudStorage
        // this.tg?.BiometricManager
        // this.tg?.Accelerometer
        // this.tg?.DeviceOrientation
        // this.tg?.Gyroscope
        // this.tg?.LocationManager

        this.tg?.disableClosingConfirmation()
        this.tg?.enableVerticalSwipes()
        this.tg?.unlockOrientation()
    }

    /**
     * Stimulus connect process
     */
    setColorTheme() {
        // object setters
        this.headerColor = this.tg?.headerColor
        this.backgroundColor = this.tg?.backgroundColor
        this.bottomBarColor = this.tg?.bottomBarColor

        // actual color setting
        this.colorTheme = this.tg?.colorScheme
        this.setTheme({ theme: this.colorTheme })
    }

    /**
     * Stimulus connect process
     */
    updateFormData() {
        this.userId = this.tg?.initDataUnsafe?.user?.id
        this.queryId = this.tg?.initDataUnsafe?.query_id

        if (null === this.form) {
            return
        }

        this.setFormHiddenFields()
    }

    /**
     * Helper
     */
    setFormHiddenFields() {
        const array = [
            { position: 'beforeend', name: 'web_app_user_id', value: this.userId },
            { position: 'beforeend', name: 'web_app_query_id', value: this.queryId },
        ]

        array.forEach(({ position, name, value }) => {
            if (undefined !== value) {
                this.form.insertAdjacentHTML(
                    position,
                    `<input type="hidden" name="${name}" value="${value}">`
                )
            }
        })
    }

    /**
     * Stimulus connect process
     */
    tgWebAppReady() {
        this.tg?.expand()
        this.tg?.ready()
    }

    /**
     * Stimulus connect process
     *
     * https://core.telegram.org/bots/webapps#events-available-for-mini-apps
     */
    registerEvents() {
        this.checkHomeScreenStatusBound = this.checkHomeScreenStatus.bind(this)

        this.tg?.checkHomeScreenStatus(this.checkHomeScreenStatusBound)
        //
        this.activatedBound = this.activated.bind(this)
        this.deactivatedBound = this.deactivated.bind(this)
        this.themeChangedBound = this.themeChanged.bind(this)
        this.viewportChangedBound = this.viewportChanged.bind(this)
        this.safeAreaChangedBound = this.safeAreaChanged.bind(this)
        this.contentSafeAreaChangedBound = this.contentSafeAreaChanged.bind(this)
        this.mainButtonClickedBound = this.mainButtonClicked.bind(this)
        this.secondaryButtonClickedBound = this.secondaryButtonClicked.bind(this)
        this.backButtonClickedBound = this.backButtonClicked.bind(this)
        this.settingsButtonClickedBound = this.settingsButtonClicked.bind(this)
        this.settingsBtnBound = this.settingsBtn.bind(this)
        this.invoiceClosedBound = this.invoiceClosed.bind(this)
        this.qrTextReceivedBound = this.qrTextReceived.bind(this)
        this.scanQrPopupClosedBound = this.scanQrPopupClosed.bind(this)
        this.clipboardTextReceivedBound = this.clipboardTextReceived.bind(this)
        this.contactRequestedBound = this.contactRequested.bind(this)

        this.tg?.SettingsButton?.onClick(this.settingsBtnBound)
        this.tg?.onEvent('activated', this.activatedBound)
        this.tg?.onEvent('deactivated', this.deactivatedBound)
        this.tg?.onEvent('themeChanged', this.themeChangedBound)
        this.tg?.onEvent('viewportChanged', this.viewportChangedBound)
        this.tg?.onEvent('safeAreaChanged', this.safeAreaChangedBound)
        this.tg?.onEvent('contentSafeAreaChanged', this.contentSafeAreaChangedBound)
        this.tg?.onEvent('mainButtonClicked', this.mainButtonClickedBound)
        this.tg?.onEvent('secondaryButtonClicked', this.secondaryButtonClickedBound)
        this.tg?.onEvent('backButtonClicked', this.backButtonClickedBound)
        this.tg?.onEvent('settingsButtonClicked', this.settingsButtonClickedBound)
        this.tg?.onEvent('invoiceClosed', this.invoiceClosedBound)
        this.tg?.onEvent('qrTextReceived', this.qrTextReceivedBound)
        this.tg?.onEvent('scanQrPopupClosed', this.scanQrPopupClosedBound)
        this.tg?.onEvent('clipboardTextReceived', this.clipboardTextReceivedBound)
        this.tg?.onEvent('contactRequested', this.contactRequestedBound)
    }

    /**
     * Stimulus disconnect process
     */
    unregisterEvents() {
        this.tg?.SettingsButton?.offClick(this.settingsBtnBound)
        this.tg?.offEvent('activated', this.activatedBound)
        this.tg?.offEvent('deactivated', this.deactivatedBound)
        this.tg?.offEvent('themeChanged', this.themeChangedBound)
        this.tg?.offEvent('viewportChanged', this.viewportChangedBound)
        this.tg?.offEvent('safeAreaChanged', this.safeAreaChangedBound)
        this.tg?.offEvent('contentSafeAreaChanged', this.contentSafeAreaChangedBound)
        this.tg?.offEvent('mainButtonClicked', this.mainButtonClickedBound)
        this.tg?.offEvent('secondaryButtonClicked', this.secondaryButtonClickedBound)
        this.tg?.offEvent('backButtonClicked', this.backButtonClickedBound)
        this.tg?.offEvent('settingsButtonClicked', this.settingsButtonClickedBound)
        this.tg?.offEvent('invoiceClosed', this.invoiceClosedBound)
        this.tg?.offEvent('qrTextReceived', this.qrTextReceivedBound)
        this.tg?.offEvent('scanQrPopupClosed', this.scanQrPopupClosedBound)
        this.tg?.offEvent('clipboardTextReceived', this.clipboardTextReceivedBound)
        this.tg?.offEvent('contactRequested', this.contactRequestedBound)
    }

    /**
     * Telegram event listener
     */
    activated() {
    }

    /**
     * Telegram event listener
     */
    deactivated() {
    }

    /**
     * Telegram event listener
     */
    qrTextReceived(event) {
        this.tg?.closeScanQrPopup()
    }

    /**
     * Telegram event listener
     */
    scanQrPopupClosed(event) {
    }

    /**
     * Telegram event listener
     * https://core.telegram.org/bots/webapps#events-available-for-mini-apps
     * https://core.telegram.org/bots/webapps#themeparams
     */
    themeChanged() {
        const theme = this.tg?.colorScheme
        const bgColor = this.tg?.themeParams?.bg_color
        this.setTheme({ theme, bgColor })
    }

    /**
     * Telegram event listener
     */
    contactRequested(event) {
    }

    /**
     * Telegram event listener
     */
    invoiceClosed(event) {
    }

    /**
     * Telegram event listener
     */
    viewportChanged({ isStateStable }) {
    }

    /**
     * Telegram event listener
     * https://core.telegram.org/bots/webapps#safeareainset
     */
    safeAreaChanged() {
        const safeAreaInset = this.tg?.safeAreaInset
    }

    /**
     * Telegram event listener
     * https://core.telegram.org/bots/webapps#contentsafeareainset
     */
    contentSafeAreaChanged() {
        const contentSafeAreaInset = this.tg?.contentSafeAreaInset
    }

    /**
     * Telegram event listener
     */
    mainButtonClicked() {
    }

    /**
     * Telegram event listener
     */
    secondaryButtonClicked() {
    }

    /**
     * Telegram event listener
     */
    backButtonClicked() {
    }

    /**
     * Telegram event listener
     */
    settingsButtonClicked() {
    }

    /**
     * Telegram event listener
     */
    settingsBtn(event) {
    }

    /**
     * Telegram event listener
     */
    clipboardTextReceived(event) {
    }

    /**
     * Telegram event listener
     */
    checkHomeScreenStatus(status) {
        if ('missed' === status || 'unknown' === status) {
            this.tg?.addToHomeScreen()
        }
    }

    /**
     * Stimulus event listener
     */
    close(event) {
        this.tg?.close()
    }

    setTheme({ theme, bgColor, color }) {
        let appThemeBgColor = null

        const callback = wonThemeEl => appThemeBgColor = themeManager.getStyleProp(wonThemeEl, themeManager.themeBgColorCssVar)
        themeManager.set({ theme, bgColor, color, callback })

        this.tg?.setHeaderColor(appThemeBgColor)
        this.tg?.setBackgroundColor(appThemeBgColor)
        this.tg?.setBottomBarColor(appThemeBgColor)
    }
}

TelegramWebApp.targets = [
    'form',
]

TelegramWebApp.values = {
    userAgent: {
        type: String, // ALWAYS { userAgent: app.request.headers.get('User-Agent') }
    },
}

export { TelegramWebApp as default }
