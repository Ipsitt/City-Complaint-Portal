import { useState } from 'react';
import Header from '@/components/Header';
import Footer from '@/components/Footer';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Badge } from '@/components/ui/badge';
import { Progress } from '@/components/ui/progress';
import { 
  Search, 
  CheckCircle, 
  Clock, 
  AlertCircle, 
  MapPin,
  Calendar,
  User,
  FileText,
  MessageSquare
} from 'lucide-react';

const Track = () => {
  const [trackingId, setTrackingId] = useState('');
  const [complaint, setComplaint] = useState(null);
  const [isLoading, setIsLoading] = useState(false);

  // Mock complaint data
  const mockComplaint = {
    id: 'CP2024001',
    title: 'Pothole on Main Street causing vehicle damage',
    category: 'Roads & Infrastructure',
    status: 'In Progress',
    priority: 'High',
    progress: 60,
    submittedDate: '2024-01-15',
    expectedResolution: '2024-01-30',
    location: '123 Main Street, Downtown',
    description: 'Large pothole approximately 2 feet wide and 6 inches deep causing damage to vehicles. Located near the intersection with Oak Street.',
    updates: [
      {
        date: '2024-01-15',
        status: 'Submitted',
        message: 'Your complaint has been received and assigned ID #CP2024001',
        icon: FileText
      },
      {
        date: '2024-01-16',
        status: 'Under Review',
        message: 'Municipal team has reviewed your report and assigned it to the Roads Department',
        icon: User
      },
      {
        date: '2024-01-18',
        status: 'Investigation Started',
        message: 'Site inspection completed. Work order created for road repair crew.',
        icon: Search
      },
      {
        date: '2024-01-22',
        status: 'In Progress',
        message: 'Repair work has begun. Estimated completion: January 30th',
        icon: Clock
      }
    ]
  };

  const handleSearch = async () => {
    if (!trackingId.trim()) return;
    
    setIsLoading(true);
    // Simulate API call
    await new Promise(resolve => setTimeout(resolve, 1500));
    
    if (trackingId.toLowerCase().includes('cp2024001')) {
      setComplaint(mockComplaint);
    } else {
      setComplaint(null);
    }
    setIsLoading(false);
  };

  const getStatusColor = (status: string) => {
    switch (status.toLowerCase()) {
      case 'submitted': return 'bg-blue-100 text-blue-800';
      case 'under review': return 'bg-yellow-100 text-yellow-800';
      case 'in progress': return 'bg-orange-100 text-orange-800';
      case 'resolved': return 'bg-green-100 text-green-800';
      case 'closed': return 'bg-gray-100 text-gray-800';
      default: return 'bg-gray-100 text-gray-800';
    }
  };

  const getPriorityColor = (priority: string) => {
    switch (priority.toLowerCase()) {
      case 'low': return 'bg-green-100 text-green-800';
      case 'medium': return 'bg-yellow-100 text-yellow-800';
      case 'high': return 'bg-red-100 text-red-800';
      case 'emergency': return 'bg-red-200 text-red-900';
      default: return 'bg-gray-100 text-gray-800';
    }
  };

  return (
    <div className="min-h-screen bg-background">
      <Header />
      
      <main className="container mx-auto px-4 py-12">
        <div className="max-w-4xl mx-auto">
          {/* Header */}
          <div className="text-center mb-12">
            <h1 className="text-4xl font-bold text-foreground mb-4">Track Your Complaint</h1>
            <p className="text-xl text-muted-foreground">
              Enter your tracking ID to view the current status and progress of your report
            </p>
          </div>

          {/* Search Form */}
          <Card className="mb-8">
            <CardHeader>
              <CardTitle className="flex items-center">
                <Search className="w-5 h-5 mr-2 text-primary" />
                Find Your Complaint
              </CardTitle>
              <CardDescription>
                Enter the tracking ID you received when submitting your report
              </CardDescription>
            </CardHeader>
            <CardContent>
              <div className="flex gap-4">
                <div className="flex-1">
                  <Label htmlFor="tracking">Tracking ID</Label>
                  <Input
                    id="tracking"
                    placeholder="e.g., CP2024001"
                    value={trackingId}
                    onChange={(e) => setTrackingId(e.target.value)}
                    className="mt-1"
                  />
                </div>
                <div className="flex items-end">
                  <Button 
                    onClick={handleSearch}
                    disabled={isLoading || !trackingId.trim()}
                    variant="civic"
                  >
                    {isLoading ? 'Searching...' : 'Track'}
                  </Button>
                </div>
              </div>
              <p className="text-sm text-muted-foreground mt-2">
                Try tracking ID: CP2024001 for demo
              </p>
            </CardContent>
          </Card>

          {/* Complaint Details */}
          {complaint && (
            <div className="space-y-6">
              {/* Status Overview */}
              <Card>
                <CardHeader>
                  <div className="flex justify-between items-start">
                    <div>
                      <CardTitle className="text-2xl">#{complaint.id}</CardTitle>
                      <CardDescription className="text-lg mt-1">
                        {complaint.title}
                      </CardDescription>
                    </div>
                    <div className="flex gap-2">
                      <Badge className={getStatusColor(complaint.status)}>
                        {complaint.status}
                      </Badge>
                      <Badge className={getPriorityColor(complaint.priority)}>
                        {complaint.priority} Priority
                      </Badge>
                    </div>
                  </div>
                </CardHeader>
                <CardContent className="space-y-6">
                  {/* Progress Bar */}
                  <div>
                    <div className="flex justify-between items-center mb-2">
                      <span className="text-sm font-medium">Progress</span>
                      <span className="text-sm text-muted-foreground">{complaint.progress}%</span>
                    </div>
                    <Progress value={complaint.progress} className="h-2" />
                  </div>

                  {/* Key Information */}
                  <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div className="space-y-4">
                      <div className="flex items-start space-x-2">
                        <Calendar className="w-4 h-4 text-muted-foreground mt-1" />
                        <div>
                          <p className="font-medium">Submitted</p>
                          <p className="text-sm text-muted-foreground">{complaint.submittedDate}</p>
                        </div>
                      </div>
                      <div className="flex items-start space-x-2">
                        <Clock className="w-4 h-4 text-muted-foreground mt-1" />
                        <div>
                          <p className="font-medium">Expected Resolution</p>
                          <p className="text-sm text-muted-foreground">{complaint.expectedResolution}</p>
                        </div>
                      </div>
                    </div>
                    <div className="space-y-4">
                      <div className="flex items-start space-x-2">
                        <MapPin className="w-4 h-4 text-muted-foreground mt-1" />
                        <div>
                          <p className="font-medium">Location</p>
                          <p className="text-sm text-muted-foreground">{complaint.location}</p>
                        </div>
                      </div>
                      <div className="flex items-start space-x-2">
                        <FileText className="w-4 h-4 text-muted-foreground mt-1" />
                        <div>
                          <p className="font-medium">Category</p>
                          <p className="text-sm text-muted-foreground">{complaint.category}</p>
                        </div>
                      </div>
                    </div>
                  </div>

                  {/* Description */}
                  <div>
                    <h4 className="font-medium mb-2">Description</h4>
                    <p className="text-sm text-muted-foreground">{complaint.description}</p>
                  </div>
                </CardContent>
              </Card>

              {/* Timeline */}
              <Card>
                <CardHeader>
                  <CardTitle className="flex items-center">
                    <MessageSquare className="w-5 h-5 mr-2 text-secondary" />
                    Status Timeline
                  </CardTitle>
                  <CardDescription>
                    Track the progress of your complaint from submission to resolution
                  </CardDescription>
                </CardHeader>
                <CardContent>
                  <div className="space-y-6">
                    {complaint.updates.map((update, index) => (
                      <div key={index} className="flex items-start space-x-4">
                        <div className="flex-shrink-0">
                          <div className="w-10 h-10 bg-primary/10 rounded-full flex items-center justify-center">
                            <update.icon className="w-5 h-5 text-primary" />
                          </div>
                        </div>
                        <div className="flex-1 min-w-0">
                          <div className="flex items-center justify-between">
                            <h4 className="font-medium">{update.status}</h4>
                            <span className="text-sm text-muted-foreground">{update.date}</span>
                          </div>
                          <p className="text-sm text-muted-foreground mt-1">{update.message}</p>
                        </div>
                      </div>
                    ))}
                  </div>
                </CardContent>
              </Card>
            </div>
          )}

          {/* No Results */}
          {trackingId && !complaint && !isLoading && (
            <Card>
              <CardContent className="text-center py-12">
                <AlertCircle className="w-12 h-12 text-muted-foreground mx-auto mb-4" />
                <h3 className="text-lg font-medium mb-2">No Complaint Found</h3>
                <p className="text-muted-foreground mb-4">
                  We couldn't find a complaint with tracking ID "{trackingId}". 
                  Please check the ID and try again.
                </p>
                <Button variant="outline" onClick={() => setTrackingId('')}>
                  Try Again
                </Button>
              </CardContent>
            </Card>
          )}
        </div>
      </main>

      <Footer />
    </div>
  );
};

export default Track;