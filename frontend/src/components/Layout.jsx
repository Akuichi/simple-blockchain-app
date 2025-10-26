import { Link, useLocation } from 'react-router-dom'

function Layout({ children }) {
  const location = useLocation()

  const isActive = (path) => {
    return location.pathname === path
      ? 'bg-primary text-white'
      : 'text-gray-700 hover:bg-gray-100'
  }

  return (
    <div className="min-h-screen bg-gray-50">
      {/* Header */}
      <header className="bg-white shadow-md">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="flex justify-between items-center py-4">
            <div className="flex items-center space-x-2">
              <div className="w-10 h-10 bg-gradient-to-r from-primary to-secondary rounded-lg flex items-center justify-center">
                <span className="text-white font-bold text-xl">⛓</span>
              </div>
              <h1 className="text-2xl font-bold text-gray-900">Blockchain App</h1>
            </div>
          </div>
        </div>
      </header>

      {/* Navigation */}
      <nav className="bg-white shadow-sm">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="flex space-x-4 py-3">
            <Link
              to="/"
              className={`px-4 py-2 rounded-lg font-medium transition-colors ${isActive('/')}`}
            >
              Dashboard
            </Link>
            <Link
              to="/transactions"
              className={`px-4 py-2 rounded-lg font-medium transition-colors ${isActive('/transactions')}`}
            >
              Transactions
            </Link>
            <Link
              to="/blocks"
              className={`px-4 py-2 rounded-lg font-medium transition-colors ${isActive('/blocks')}`}
            >
              Blockchain
            </Link>
          </div>
        </div>
      </nav>

      {/* Main Content */}
      <main className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        {children}
      </main>

      {/* Footer */}
      <footer className="bg-white border-t mt-12">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
          <p className="text-center text-gray-600 text-sm">
            Simple Blockchain Application - Built with Laravel 12 & React
          </p>
        </div>
      </footer>
    </div>
  )
}

export default Layout
