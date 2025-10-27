import { useState, useEffect } from 'react'
import { validateBlockchain, getBlockchainStats, mineBlock, tamperBlock, rebuildBlockchain } from '../services/api'

function Dashboard() {
  const [stats, setStats] = useState(null)
  const [validation, setValidation] = useState(null)
  const [loading, setLoading] = useState(false)
  const [mining, setMining] = useState(false)
  const [tampering, setTampering] = useState(false)
  const [rebuilding, setRebuilding] = useState(false)

  useEffect(() => {
    fetchStats()
    checkValidation()
  }, [])

  const fetchStats = async () => {
    try {
      const response = await getBlockchainStats()
      setStats(response.data.data)
    } catch (error) {
      console.error('Error fetching stats:', error)
    }
  }

  const checkValidation = async () => {
    try {
      setLoading(true)
      const response = await validateBlockchain()
      setValidation(response.data)
    } catch (error) {
      console.error('Error validating blockchain:', error)
      setValidation({ success: false, message: 'Failed to validate blockchain' })
    } finally {
      setLoading(false)
    }
  }

  const handleMineBlock = async () => {
    try {
      setMining(true)
      const response = await mineBlock()
      if (response.data.success) {
        alert('Block mined successfully!')
        fetchStats()
        checkValidation()
      }
    } catch (error) {
      const message = error.response?.data?.message || 'Failed to mine block'
      alert(message)
    } finally {
      setMining(false)
    }
  }

  const handleTamperBlock = async () => {
    if (!stats?.last_block) {
      alert('No blocks available to tamper with')
      return
    }

    const confirmTamper = window.confirm(
      '⚠️ This will tamper with the last block to demonstrate blockchain immutability.\n\n' +
      'After tampering:\n' +
      '• The blockchain validation will fail\n' +
      '• This demonstrates how blockchain detects tampering\n' +
      '• You can fix it by clicking "Rebuild Chain"\n\n' +
      'Continue with tampering?'
    )

    if (!confirmTamper) return

    try {
      setTampering(true)
      const response = await tamperBlock(stats.last_block.id)
      if (response.data.success) {
        alert(
          '🔓 Block tampered successfully!\n\n' +
          `Original hash: ${response.data.data.original_hash}\n` +
          `Tampered hash: ${response.data.data.tampered_hash}\n\n` +
          'Now click "Validate Chain" to see the errors, then "Rebuild Chain" to fix!'
        )
        fetchStats()
        checkValidation()
      }
    } catch (error) {
      const message = error.response?.data?.message || 'Failed to tamper with block'
      alert(message)
    } finally {
      setTampering(false)
    }
  }

  const handleRebuildChain = async () => {
    const confirmRebuild = window.confirm(
      '🔧 This will rebuild the blockchain from the first invalid block.\n\n' +
      'The rebuild process will:\n' +
      '• Recalculate all block hashes\n' +
      '• Re-mine blocks with proof of work\n' +
      '• Restore blockchain integrity\n\n' +
      'Continue with rebuild?'
    )

    if (!confirmRebuild) return

    try {
      setRebuilding(true)
      const response = await rebuildBlockchain()
      if (response.data.success) {
        alert(
          '✅ Chain rebuilt successfully!\n\n' +
          response.data.message + '\n\n' +
          'The blockchain is now valid again!'
        )
        fetchStats()
        checkValidation()
      }
    } catch (error) {
      const message = error.response?.data?.message || 'Failed to rebuild chain'
      alert(message)
    } finally {
      setRebuilding(false)
    }
  }

  return (
    <div className="space-y-6">
      <div className="flex justify-between items-center">
        <h2 className="text-3xl font-bold text-gray-900">Dashboard</h2>
        <div className="flex space-x-3">
          <button
            onClick={handleMineBlock}
            disabled={mining}
            className="btn btn-success disabled:opacity-50 disabled:cursor-not-allowed"
          >
            {mining ? 'Mining...' : '⛏️ Mine Block'}
          </button>
          <button
            onClick={handleTamperBlock}
            disabled={tampering || !stats?.last_block || stats?.last_block?.index === 0}
            className="btn bg-red-500 hover:bg-red-600 text-white disabled:opacity-50 disabled:cursor-not-allowed"
            title="Tamper with last block to demonstrate immutability"
          >
            {tampering ? 'Tampering...' : '🔓 Tamper Block'}
          </button>
          <button
            onClick={handleRebuildChain}
            disabled={rebuilding || validation?.data?.valid !== false}
            className="btn bg-orange-500 hover:bg-orange-600 text-white disabled:opacity-50 disabled:cursor-not-allowed"
            title="Rebuild chain from first invalid block"
          >
            {rebuilding ? 'Rebuilding...' : '🔧 Rebuild Chain'}
          </button>
          <button
            onClick={checkValidation}
            disabled={loading}
            className="btn btn-primary disabled:opacity-50 disabled:cursor-not-allowed"
          >
            {loading ? 'Validating...' : '🔍 Validate Chain'}
          </button>
        </div>
      </div>

      {/* Blockchain Status */}
      {validation && (
        <div
          className={`card ${
            validation.data?.valid
              ? 'bg-green-50 border-2 border-green-200'
              : 'bg-red-50 border-2 border-red-200'
          }`}
        >
          <div className="flex items-center justify-between">
            <div>
              <h3 className="text-xl font-bold mb-2">
                {validation.data?.valid ? '✅ Blockchain is Valid' : '⚠️ Blockchain is Invalid'}
              </h3>
              <p className={validation.data?.valid ? 'text-green-700' : 'text-red-700'}>
                {validation.data?.valid 
                  ? `All ${validation.data?.blocks_checked || 0} blocks verified successfully!`
                  : 'Chain validation failed - tampering detected!'}
              </p>
              {validation.data?.errors && validation.data.errors.length > 0 && (
                <ul className="mt-2 text-red-600 text-sm">
                  {validation.data.errors.map((error, index) => (
                    <li key={index}>• {error}</li>
                  ))}
                </ul>
              )}
            </div>
            <div className="text-5xl">
              {validation.data?.valid ? '🔒' : '🔓'}
            </div>
          </div>
        </div>
      )}

      {/* Statistics Grid */}
      {stats && (
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
          <div className="card bg-gradient-to-br from-blue-500 to-blue-600 text-white">
            <h3 className="text-sm font-medium opacity-90">Total Blocks</h3>
            <p className="text-4xl font-bold mt-2">{stats.total_blocks}</p>
          </div>

          <div className="card bg-gradient-to-br from-purple-500 to-purple-600 text-white">
            <h3 className="text-sm font-medium opacity-90">Total Transactions</h3>
            <p className="text-4xl font-bold mt-2">{stats.total_transactions}</p>
          </div>

          <div className="card bg-gradient-to-br from-yellow-500 to-yellow-600 text-white">
            <h3 className="text-sm font-medium opacity-90">Pending Transactions</h3>
            <p className="text-4xl font-bold mt-2">{stats.pending_transactions}</p>
          </div>

          <div className="card bg-gradient-to-br from-green-500 to-green-600 text-white">
            <h3 className="text-sm font-medium opacity-90">Mined Transactions</h3>
            <p className="text-4xl font-bold mt-2">{stats.mined_transactions}</p>
          </div>
        </div>
      )}

      {/* Last Block Info */}
      {stats?.last_block && (
        <div className="card">
          <h3 className="text-xl font-bold mb-4">Last Block</h3>
          <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
              <p className="text-sm text-gray-600">Block Index</p>
              <p className="font-mono font-bold text-lg">{stats.last_block.index}</p>
            </div>
            <div>
              <p className="text-sm text-gray-600">Timestamp</p>
              <p className="font-mono">
                {new Date(stats.last_block.timestamp).toLocaleString()}
              </p>
            </div>
            <div>
              <p className="text-sm text-gray-600">Difficulty</p>
              <p className="font-mono font-bold text-lg">{stats.difficulty}</p>
            </div>
          </div>
          <div className="mt-4">
            <p className="text-sm text-gray-600">Hash</p>
            <p className="font-mono text-xs break-all bg-gray-100 p-2 rounded mt-1">
              {stats.last_block.hash}
            </p>
          </div>
        </div>
      )}
    </div>
  )
}

export default Dashboard
