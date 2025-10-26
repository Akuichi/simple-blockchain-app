import { useState, useEffect } from 'react'
import { getAllBlocks } from '../services/api'

function Blocks() {
  const [blocks, setBlocks] = useState([])
  const [loading, setLoading] = useState(true)
  const [selectedBlock, setSelectedBlock] = useState(null)

  useEffect(() => {
    fetchBlocks()
  }, [])

  const fetchBlocks = async () => {
    try {
      setLoading(true)
      const response = await getAllBlocks()
      setBlocks(response.data.data)
    } catch (error) {
      console.error('Error fetching blocks:', error)
    } finally {
      setLoading(false)
    }
  }

  const handleBlockClick = (block) => {
    setSelectedBlock(selectedBlock?.id === block.id ? null : block)
  }

  if (loading) {
    return (
      <div className="flex justify-center items-center py-12">
        <div className="text-xl text-gray-600">Loading blockchain...</div>
      </div>
    )
  }

  return (
    <div className="space-y-6">
      <div className="flex justify-between items-center">
        <h2 className="text-3xl font-bold text-gray-900">Blockchain</h2>
        <button
          onClick={fetchBlocks}
          className="btn btn-secondary"
        >
          Refresh
        </button>
      </div>

      {blocks.length === 0 ? (
        <div className="card text-center py-12">
          <p className="text-gray-500 text-lg">No blocks in the blockchain yet</p>
          <p className="text-gray-400 text-sm mt-2">Create some transactions and mine a block to get started</p>
        </div>
      ) : (
        <div className="space-y-4">
          {blocks.map((block, index) => (
            <div key={block.id} className="relative">
              {/* Connection Line */}
              {index > 0 && (
                <div className="absolute left-8 -top-4 w-0.5 h-4 bg-gray-300"></div>
              )}

              {/* Block Card */}
              <div
                className={`card cursor-pointer transition-all hover:shadow-lg ${
                  block.index_no === 0
                    ? 'border-2 border-purple-300 bg-purple-50'
                    : 'border-2 border-gray-200'
                } ${selectedBlock?.id === block.id ? 'ring-2 ring-primary' : ''}`}
                onClick={() => handleBlockClick(block)}
              >
                <div className="flex items-start space-x-4">
                  {/* Block Icon */}
                  <div
                    className={`w-16 h-16 rounded-lg flex items-center justify-center text-2xl flex-shrink-0 ${
                      block.index_no === 0
                        ? 'bg-purple-500 text-white'
                        : 'bg-gradient-to-br from-primary to-secondary text-white'
                    }`}
                  >
                    {block.index_no === 0 ? '🏁' : '⬜'}
                  </div>

                  {/* Block Info */}
                  <div className="flex-1 min-w-0">
                    <div className="flex justify-between items-start mb-3">
                      <div>
                        <h3 className="text-xl font-bold">
                          {block.index_no === 0 ? 'Genesis Block' : `Block #${block.index_no}`}
                        </h3>
                        <p className="text-sm text-gray-500">
                          {new Date(block.timestamp).toLocaleString()}
                        </p>
                      </div>
                      <div className="text-right">
                        <p className="text-sm text-gray-600">Transactions</p>
                        <p className="text-2xl font-bold text-primary">
                          {block.transactions?.length || 0}
                        </p>
                      </div>
                    </div>

                    <div className="grid grid-cols-1 md:grid-cols-2 gap-3 text-sm">
                      <div>
                        <p className="text-gray-600 font-medium">Current Hash</p>
                        <p className="font-mono text-xs break-all bg-gray-100 p-2 rounded mt-1">
                          {block.current_hash}
                        </p>
                      </div>
                      <div>
                        <p className="text-gray-600 font-medium">Previous Hash</p>
                        <p className="font-mono text-xs break-all bg-gray-100 p-2 rounded mt-1">
                          {block.previous_hash}
                        </p>
                      </div>
                    </div>

                    <div className="mt-3 flex items-center space-x-4 text-sm">
                      <div>
                        <span className="text-gray-600">Nonce: </span>
                        <span className="font-mono font-bold">{block.nonce}</span>
                      </div>
                      <div>
                        <span className="text-gray-600">Index: </span>
                        <span className="font-bold">{block.index_no}</span>
                      </div>
                    </div>
                  </div>
                </div>

                {/* Expanded Transactions */}
                {selectedBlock?.id === block.id && block.transactions && block.transactions.length > 0 && (
                  <div className="mt-4 pt-4 border-t border-gray-200">
                    <h4 className="font-bold mb-3">Transactions in this block:</h4>
                    <div className="space-y-2">
                      {block.transactions.map((transaction) => (
                        <div
                          key={transaction.id}
                          className="bg-white border border-gray-200 rounded-lg p-3 text-sm"
                        >
                          <div className="grid grid-cols-4 gap-3">
                            <div>
                              <p className="text-gray-600">ID</p>
                              <p className="font-mono">{transaction.id}</p>
                            </div>
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
                              <p className="font-bold text-green-600">${transaction.amount}</p>
                            </div>
                          </div>
                        </div>
                      ))}
                    </div>
                  </div>
                )}
              </div>
            </div>
          ))}
        </div>
      )}
    </div>
  )
}

export default Blocks
