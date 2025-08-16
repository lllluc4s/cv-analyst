/**
 * Dynamic URL Configuration
 * Centralizes all URL management for the frontend application
 */

// Get the base API URL from environment variables
const getApiUrl = (): string => {
  return import.meta.env.VITE_API_URL || 'http://localhost:8001'
}

// Get the base frontend URL
const getFrontendUrl = (): string => {
  return import.meta.env.VITE_FRONTEND_URL || import.meta.env.VITE_PRODUCTION_FRONTEND_URL || window.location.origin
}

/**
 * URL Builder utility class
 */
export class UrlBuilder {
  private static apiBaseUrl = getApiUrl()
  private static frontendBaseUrl = getFrontendUrl()

  /**
   * Build API URL with optional path
   */
  static api(path: string = ''): string {
    const baseUrl = this.apiBaseUrl.replace(/\/$/, '')
    const apiPath = path ? `/api/${path.replace(/^\//, '')}` : '/api'
    return `${baseUrl}${apiPath}`
  }

  /**
   * Build frontend URL with optional path
   */
  static frontend(path: string = ''): string {
    const baseUrl = this.frontendBaseUrl.replace(/\/$/, '')
    const frontendPath = path ? `/${path.replace(/^\//, '')}` : ''
    return `${baseUrl}${frontendPath}`
  }

  /**
   * Build storage URL for files
   */
  static storage(path: string = ''): string {
    const baseUrl = this.apiBaseUrl.replace(/\/$/, '')
    const storagePath = path ? `/storage/${path.replace(/^\//, '')}` : '/storage'
    return `${baseUrl}${storagePath}`
  }

  /**
   * Build files URL for PDFs and documents
   */
  static files(path: string = ''): string {
    const baseUrl = this.apiBaseUrl.replace(/\/$/, '')
    const filesPath = path ? `/files/${path.replace(/^\//, '')}` : '/files'
    return `${baseUrl}${filesPath}`
  }

  /**
   * Build social URLs for sharing
   */
  static social(path: string = ''): string {
    const baseUrl = this.apiBaseUrl.replace(/\/$/, '')
    const socialPath = path ? `/social/${path.replace(/^\//, '')}` : '/social'
    return `${baseUrl}${socialPath}`
  }

  /**
   * Get the current API base URL
   */
  static getApiBaseUrl(): string {
    return this.apiBaseUrl
  }

  /**
   * Get the current frontend base URL
   */
  static getFrontendBaseUrl(): string {
    return this.frontendBaseUrl
  }

  /**
   * Update URLs (useful for dynamic environments)
   */
  static updateUrls(apiUrl?: string, frontendUrl?: string): void {
    if (apiUrl) this.apiBaseUrl = apiUrl
    if (frontendUrl) this.frontendBaseUrl = frontendUrl
  }
}

// Export individual functions for convenience
export const apiUrl = (path?: string) => UrlBuilder.api(path)
export const frontendUrl = (path?: string) => UrlBuilder.frontend(path)
export const storageUrl = (path?: string) => UrlBuilder.storage(path)
export const filesUrl = (path?: string) => UrlBuilder.files(path)
export const socialUrl = (path?: string) => UrlBuilder.social(path)

// Export the base URLs as constants
export const API_BASE_URL = UrlBuilder.getApiBaseUrl()
export const FRONTEND_BASE_URL = UrlBuilder.getFrontendBaseUrl()

export default UrlBuilder
