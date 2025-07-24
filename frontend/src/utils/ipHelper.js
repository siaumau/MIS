/**
 * IP 地址獲取工具
 */

// 獲取本機 IP 地址的多種方法
export class IPHelper {
  static cachedIP = null
  static isLoading = false
  static callbacks = []

  /**
   * 通過 WebRTC 獲取本機 IP
   */
  static async getLocalIPByWebRTC() {
    return new Promise((resolve, reject) => {
      const rtc = new RTCPeerConnection({ iceServers: [] })
      let localIP = null

      rtc.createDataChannel('')
      
      rtc.onicecandidate = (event) => {
        if (event.candidate) {
          const candidate = event.candidate.candidate
          const ipMatch = candidate.match(/([0-9]{1,3}(\.[0-9]{1,3}){3}|[a-f0-9]{1,4}(:[a-f0-9]{1,4}){7})/)
          
          if (ipMatch && ipMatch[1] && !localIP) {
            localIP = ipMatch[1]
            // 過濾掉回環地址和特殊地址
            if (!localIP.startsWith('127.') && 
                !localIP.startsWith('169.254.') && 
                !localIP.includes('::1')) {
              rtc.close()
              resolve(localIP)
            }
          }
        }
      }

      rtc.createOffer()
        .then(offer => rtc.setLocalDescription(offer))
        .catch(reject)

      // 超時處理
      setTimeout(() => {
        rtc.close()
        if (!localIP) {
          reject(new Error('WebRTC IP detection timeout'))
        }
      }, 3000)
    })
  }

  /**
   * 通過第三方服務獲取 IP（備用方案）
   */
  static async getIPByService() {
    const services = [
      'https://api.ipify.org?format=json',
      'https://ipapi.co/json/',
      'https://ipinfo.io/json'
    ]

    for (const service of services) {
      try {
        const response = await fetch(service, { timeout: 2000 })
        const data = await response.json()
        
        // 根據不同服務的響應格式獲取 IP
        const ip = data.ip || data.query || data.ipAddress
        if (ip && this.isValidIP(ip)) {
          return ip
        }
      } catch (error) {
        console.warn(`Service ${service} failed:`, error)
        continue
      }
    }
    
    throw new Error('All IP services failed')
  }

  /**
   * 驗證 IP 地址格式
   */
  static isValidIP(ip) {
    const ipv4Regex = /^(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$/
    const ipv6Regex = /^(?:[0-9a-fA-F]{1,4}:){7}[0-9a-fA-F]{1,4}$/
    return ipv4Regex.test(ip) || ipv6Regex.test(ip)
  }

  /**
   * 獲取本機 IP 地址（主要方法）
   */
  static async getLocalIP() {
    // 如果有緩存且未過期，直接返回
    if (this.cachedIP) {
      return this.cachedIP
    }

    // 如果正在加載，等待結果
    if (this.isLoading) {
      return new Promise((resolve) => {
        this.callbacks.push(resolve)
      })
    }

    this.isLoading = true

    try {
      let ip = null

      // 優先使用 WebRTC 方法獲取本機內網 IP
      try {
        ip = await this.getLocalIPByWebRTC()
        console.log('Got IP via WebRTC:', ip)
      } catch (error) {
        console.warn('WebRTC IP detection failed:', error)
        
        // 備用方法：使用第三方服務
        try {
          ip = await this.getIPByService()
          console.log('Got IP via service:', ip)
        } catch (serviceError) {
          console.warn('Service IP detection failed:', serviceError)
          
          // 最後備用：使用默認本機地址
          ip = '192.168.1.100' // 可配置的默認值
          console.log('Using default IP:', ip)
        }
      }

      // 緩存結果（5分鐘有效期）
      this.cachedIP = ip
      setTimeout(() => {
        this.cachedIP = null
      }, 5 * 60 * 1000)

      // 通知等待的回調
      this.callbacks.forEach(callback => callback(ip))
      this.callbacks = []

      return ip
    } finally {
      this.isLoading = false
    }
  }

  /**
   * 手動刷新 IP 緩存
   */
  static refreshIP() {
    this.cachedIP = null
    return this.getLocalIP()
  }

  /**
   * 構建完整的 API URL
   */
  static async buildApiUrl(port = 8000, path = '/api') {
    const ip = await this.getLocalIP()
    return `http://${ip}:${port}${path}`
  }
}

// 導出便捷方法
export const getLocalIP = () => IPHelper.getLocalIP()
export const buildApiUrl = (port, path) => IPHelper.buildApiUrl(port, path)
export const refreshIP = () => IPHelper.refreshIP()