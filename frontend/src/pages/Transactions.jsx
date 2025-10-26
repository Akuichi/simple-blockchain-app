import { useState, useEffect } from 'react'
import { createTransaction, getPendingTransactions, getAllTransactions } from '../services/api'

function Transactions() {
  const [formData, setFormData] = useState({
    sender: '',
    receiver: '',
    amount: '',
  })
  const [pendingTransactions, setPendingTransactions] = useState([])
  const [allTransactions, setAllTransactions] = useState([])
  const [loading, setLoading] = useState(false)
  const [activeTab, setActiveTab] = useState('pending')

  useEffect(() => {
    fetchTransactions()
  }, [])

  const fetchTransactions = async () => {
    try {
      const [pendingRes, allRes] = await Promise.all([
        getPendingTransactions(),
        getAllTransactions(),
      ])
      setPendingTransactions(pendingRes.data.data)
      setAllTransactions(allRes.data.data)
    } catch (error) {
      console.error('Error fetching transactions:', error)
    }
  }

  const handleSubmit = async (e) => {
    e.preventDefault()
    try {
      setLoading(true)
      const response = await createTransaction(formData)
      if (response.data.success) {
        alert('Transaction created successfully!')
        setFormData({ sender: '', receiver: '', amount: '' })
        fetchTransactions()
      }
    } catch (error) {
      const errors = error.response?.data?.errors
      if (errors) {
        const errorMessages = Object.values(errors).flat().join('\n')
        alert(errorMessages)
      } else {
        alert('Failed to create transaction')
      }
    } finally {
      setLoading(false)
    }
  }

  const handleChange = (e) => {
    setFormData({
      ...formData,
      [e.target.name]: e.target.value,
    })
  }

  return (
    <div className="space-y-6">
      <h2 className="text-3xl font-bold text-gray-900">Transactions</h2>

      {/* Create Transaction Form */}
      <div className="card">
        <h3 className="text-xl font-bold mb-4">Create New Transaction</h3>
        <form onSubmit={handleSubmit} className="space-y-4">
          <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
              <label className="label">Sender</label>
              <input
                type="text"
                name="sender"
                value={formData.sender}
                onChange={handleChange}
                className="input"
                placeholder="Alice"
                required
              />
            </div>
            <div>
              <label className="label">Receiver</label>
              <input
                type="text"
                name="receiver"
                value={formData.receiver}
                onChange={handleChange}
                className="input"
                placeholder="Bob"
                required
              />
            </div>
            <div>
              <label className="label">Amount</label>
              <input
                type="number"
                name="amount"
                value={formData.amount}
                onChange={handleChange}
                className="input"
                placeholder="100.00"
                step="0.01"
                min="0.01"
                required
              />
            </div>
          </div>
          <button
            type="submit"
            disabled={loading}
            className="btn btn-primary disabled:opacity-50 disabled:cursor-not-allowed"
          >
            {loading ? 'Creating...' : 'Create Transaction'}
          </button>
        </form>
      </div>

      {/* Tabs */}
      <div className="flex space-x-4 border-b">
        <button
          onClick={() => setActiveTab('pending')}
          className={`px-4 py-2 font-medium transition-colors ${
            activeTab === 'pending'
              ? 'border-b-2 border-primary text-primary'
              : 'text-gray-600 hover:text-gray-900'
          }`}
        >
          Pending ({pendingTransactions.length})
        </button>
        <button
          onClick={() => setActiveTab('all')}
          className={`px-4 py-2 font-medium transition-colors ${
            activeTab === 'all'
              ? 'border-b-2 border-primary text-primary'
              : 'text-gray-600 hover:text-gray-900'
          }`}
        >
          All Transactions ({allTransactions.length})
        </button>
      </div>

      {/* Transaction List */}
      <div className="card">
        {activeTab === 'pending' ? (
          pendingTransactions.length === 0 ? (
            <p className="text-center text-gray-500 py-8">No pending transactions</p>
          ) : (
            <div className="space-y-3">
              {pendingTransactions.map((transaction) => (
                <div
                  key={transaction.id}
                  className="border border-yellow-200 bg-yellow-50 rounded-lg p-4"
                >
                  <div className="flex justify-between items-start">
                    <div className="flex-1">
                      <div className="flex items-center space-x-2 mb-2">
                        <span className="px-2 py-1 bg-yellow-200 text-yellow-800 rounded text-xs font-medium">
                          PENDING
                        </span>
                        <span className="text-gray-500 text-sm">
                          ID: {transaction.id}
                        </span>
                      </div>
                      <div className="grid grid-cols-3 gap-4 text-sm">
                        <div>
                          <p className="text-gray-600">From</p>
                          <p className="font-medium">{transaction.sender}</p>
                        </div>
                        <div>
                          <p className="text-gray-600">To</p>
                          <p className="font-medium">{transaction.receiver}</p>
                        </div>
                        <div>
                          <p className="text-gray-600">Amount</p>
                          <p className="font-bold text-lg">${transaction.amount}</p>
                        </div>
                      </div>
                    </div>
                  </div>
                  <p className="text-xs text-gray-500 mt-2">
                    {new Date(transaction.timestamp).toLocaleString()}
                  </p>
                </div>
              ))}
            </div>
          )
        ) : allTransactions.length === 0 ? (
          <p className="text-center text-gray-500 py-8">No transactions yet</p>
        ) : (
          <div className="space-y-3">
            {allTransactions.map((transaction) => (
              <div
                key={transaction.id}
                className={`border rounded-lg p-4 ${
                  transaction.status === 'mined'
                    ? 'border-green-200 bg-green-50'
                    : 'border-yellow-200 bg-yellow-50'
                }`}
              >
                <div className="flex justify-between items-start">
                  <div className="flex-1">
                    <div className="flex items-center space-x-2 mb-2">
                      <span
                        className={`px-2 py-1 rounded text-xs font-medium ${
                          transaction.status === 'mined'
                            ? 'bg-green-200 text-green-800'
                            : 'bg-yellow-200 text-yellow-800'
                        }`}
                      >
                        {transaction.status.toUpperCase()}
                      </span>
                      <span className="text-gray-500 text-sm">
                        ID: {transaction.id}
                      </span>
                      {transaction.blocks && transaction.blocks.length > 0 && (
                        <span className="text-gray-500 text-sm">
                          Block: {transaction.blocks[0].index_no}
                        </span>
                      )}
                    </div>
                    <div className="grid grid-cols-3 gap-4 text-sm">
                      <div>
                        <p className="text-gray-600">From</p>
                        <p className="font-medium">{transaction.sender}</p>
                      </div>
                      <div>
                        <p className="text-gray-600">To</p>
                        <p className="font-medium">{transaction.receiver}</p>
                      </div>
                      <div>
                        <p className="text-gray-600">Amount</p>
                        <p className="font-bold text-lg">${transaction.amount}</p>
                      </div>
                    </div>
                  </div>
                </div>
                <p className="text-xs text-gray-500 mt-2">
                  {new Date(transaction.timestamp).toLocaleString()}
                </p>
              </div>
            ))}
          </div>
        )}
      </div>
    </div>
  )
}

export default Transactions
