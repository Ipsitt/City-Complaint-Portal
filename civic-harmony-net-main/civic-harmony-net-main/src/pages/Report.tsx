import { useState } from 'react';
import Header from '@/components/Header';
import Footer from '@/components/Footer';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { useToast } from '@/hooks/use-toast';
import { 
  Upload, 
  MapPin, 
  AlertTriangle, 
  Construction, 
  Zap, 
  Droplets, 
  Car,
  TreePine,
  Building
} from 'lucide-react';

const Report = () => {
  const { toast } = useToast();
  const [isSubmitting, setIsSubmitting] = useState(false);
  const [formData, setFormData] = useState({
    category: '',
    title: '',
    description: '',
    location: '',
    urgency: '',
    contact: '',
    anonymous: false
  });

  const categories = [
    { value: 'roads', label: 'Roads & Infrastructure', icon: Construction },
    { value: 'electrical', label: 'Electrical Hazards', icon: Zap },
    { value: 'water', label: 'Water & Drainage', icon: Droplets },
    { value: 'traffic', label: 'Traffic Violations', icon: Car },
    { value: 'environment', label: 'Environmental Issues', icon: TreePine },
    { value: 'buildings', label: 'Building Safety', icon: Building },
  ];

  const urgencyLevels = [
    { value: 'low', label: 'Low Priority', color: 'text-green-600' },
    { value: 'medium', label: 'Medium Priority', color: 'text-yellow-600' },
    { value: 'high', label: 'High Priority', color: 'text-red-600' },
    { value: 'emergency', label: 'Emergency', color: 'text-red-800' },
  ];

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setIsSubmitting(true);

    // Simulate API call
    await new Promise(resolve => setTimeout(resolve, 2000));

    toast({
      title: "Report Submitted Successfully!",
      description: "Your complaint has been registered. Tracking ID: #CP2024001",
    });

    setIsSubmitting(false);
    setFormData({
      category: '',
      title: '',
      description: '',
      location: '',
      urgency: '',
      contact: '',
      anonymous: false
    });
  };

  return (
    <div className="min-h-screen bg-background">
      <Header />
      
      <main className="container mx-auto px-4 py-12">
        <div className="max-w-4xl mx-auto">
          {/* Header */}
          <div className="text-center mb-12">
            <h1 className="text-4xl font-bold text-foreground mb-4">Report an Issue</h1>
            <p className="text-xl text-muted-foreground">
              Help us improve your community by reporting problems that need attention
            </p>
          </div>

          {/* Category Selection */}
          <Card className="mb-8">
            <CardHeader>
              <CardTitle className="flex items-center">
                <AlertTriangle className="w-5 h-5 mr-2 text-secondary" />
                What type of issue are you reporting?
              </CardTitle>
              <CardDescription>
                Select the category that best describes your concern
              </CardDescription>
            </CardHeader>
            <CardContent>
              <div className="grid grid-cols-2 md:grid-cols-3 gap-4">
                {categories.map((category) => (
                  <button
                    key={category.value}
                    onClick={() => setFormData({ ...formData, category: category.value })}
                    className={`p-4 border rounded-lg text-left transition-all hover:shadow-md ${
                      formData.category === category.value
                        ? 'border-primary bg-primary/5'
                        : 'border-border hover:border-primary/50'
                    }`}
                  >
                    <category.icon className={`w-6 h-6 mb-2 ${
                      formData.category === category.value ? 'text-primary' : 'text-muted-foreground'
                    }`} />
                    <div className="font-medium text-sm">{category.label}</div>
                  </button>
                ))}
              </div>
            </CardContent>
          </Card>

          {/* Report Form */}
          <Card>
            <CardHeader>
              <CardTitle>Issue Details</CardTitle>
              <CardDescription>
                Provide detailed information to help authorities address the issue effectively
              </CardDescription>
            </CardHeader>
            <CardContent>
              <form onSubmit={handleSubmit} className="space-y-6">
                {/* Title */}
                <div>
                  <Label htmlFor="title">Issue Title *</Label>
                  <Input
                    id="title"
                    placeholder="Brief description of the issue"
                    value={formData.title}
                    onChange={(e) => setFormData({ ...formData, title: e.target.value })}
                    required
                  />
                </div>

                {/* Description */}
                <div>
                  <Label htmlFor="description">Detailed Description *</Label>
                  <Textarea
                    id="description"
                    placeholder="Provide detailed information about the issue, including when you noticed it and any relevant circumstances..."
                    rows={4}
                    value={formData.description}
                    onChange={(e) => setFormData({ ...formData, description: e.target.value })}
                    required
                  />
                </div>

                {/* Location */}
                <div>
                  <Label htmlFor="location">Location *</Label>
                  <div className="relative">
                    <MapPin className="absolute left-3 top-3 w-4 h-4 text-muted-foreground" />
                    <Input
                      id="location"
                      placeholder="Street address or nearby landmark"
                      className="pl-10"
                      value={formData.location}
                      onChange={(e) => setFormData({ ...formData, location: e.target.value })}
                      required
                    />
                  </div>
                </div>

                {/* Priority Level */}
                <div>
                  <Label htmlFor="urgency">Priority Level *</Label>
                  <Select onValueChange={(value) => setFormData({ ...formData, urgency: value })}>
                    <SelectTrigger>
                      <SelectValue placeholder="Select urgency level" />
                    </SelectTrigger>
                    <SelectContent>
                      {urgencyLevels.map((level) => (
                        <SelectItem key={level.value} value={level.value}>
                          <span className={level.color}>{level.label}</span>
                        </SelectItem>
                      ))}
                    </SelectContent>
                  </Select>
                </div>

                {/* Photo Upload */}
                <div>
                  <Label htmlFor="photos">Upload Photos</Label>
                  <div className="border-2 border-dashed border-border rounded-lg p-8 text-center hover:border-primary/50 transition-colors">
                    <Upload className="w-8 h-8 text-muted-foreground mx-auto mb-4" />
                    <p className="text-muted-foreground mb-2">Click to upload photos or drag and drop</p>
                    <p className="text-sm text-muted-foreground">PNG, JPG up to 10MB each</p>
                    <input type="file" multiple accept="image/*" className="hidden" id="photos" />
                  </div>
                </div>

                {/* Contact Information */}
                <div>
                  <Label htmlFor="contact">Contact Information (Optional)</Label>
                  <Input
                    id="contact"
                    placeholder="Email or phone number for updates"
                    value={formData.contact}
                    onChange={(e) => setFormData({ ...formData, contact: e.target.value })}
                  />
                  <p className="text-sm text-muted-foreground mt-1">
                    Leave blank to submit anonymously
                  </p>
                </div>

                {/* Submit Button */}
                <div className="flex justify-end space-x-4 pt-6">
                  <Button variant="outline" type="button">
                    Save as Draft
                  </Button>
                  <Button 
                    type="submit" 
                    variant="civic" 
                    disabled={isSubmitting || !formData.category || !formData.title || !formData.description || !formData.location || !formData.urgency}
                  >
                    {isSubmitting ? 'Submitting...' : 'Submit Report'}
                  </Button>
                </div>
              </form>
            </CardContent>
          </Card>
        </div>
      </main>

      <Footer />
    </div>
  );
};

export default Report;